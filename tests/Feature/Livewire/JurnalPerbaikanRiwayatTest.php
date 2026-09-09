<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Keuangan\JurnalPerbaikanRiwayat;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The audit trail of edited journals.
 *
 * jurnal_backup lives in mysql_smc while the journal and the member of staff it
 * names live in mysql_sik, so neither relation can carry a foreign key — the
 * table has none at all. Both are nullable columns pointing across the database
 * boundary, and the whole purpose of the table is to outlive the journal rows it
 * refers to.
 *
 * RouteSweepTest passes on this page because an empty table renders an empty
 * table. What breaks it is a row that exists and points at something gone.
 */
class JurnalPerbaikanRiwayatTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::connection('mysql_smc')->table('jurnal_backup')->where('no_jurnal', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function backupRow(string $noJurnal, string $nip): void
    {
        DB::connection('mysql_smc')->table('jurnal_backup')->insert([
            'no_jurnal'         => $noJurnal,
            'tgl_jurnal_asli'   => now()->toDateString(),
            'tgl_jurnal_diubah' => now()->toDateString(),
            'nip'               => $nip,
        ]);
    }

    /**
     * @test
     *
     * A journal deleted after being edited leaves the backup row behind. The
     * page has to render it rather than fall over on the missing relation.
     */
    public function renders_a_row_whose_journal_no_longer_exists(): void
    {
        $petugas = $this->petugasWithPermissions(
            ['keuangan.jurnal-perbaikan-riwayat.read'],
            '99999901'
        );

        $this->backupRow('UJI-TIDAKADA', '99999901');

        Livewire::actingAs($petugas)
            ->test(JurnalPerbaikanRiwayat::class)
            ->call('loadProperties')
            ->assertOk()
            ->assertSee('UJI-TIDAKADA');
    }

    /**
     * @test
     *
     * The NIP is a plain string column, so it can name somebody who has since
     * left and been removed from pegawai.
     */
    public function renders_a_row_whose_petugas_no_longer_exists(): void
    {
        $petugas = $this->petugasWithPermissions(
            ['keuangan.jurnal-perbaikan-riwayat.read'],
            '99999901'
        );

        $this->backupRow('UJI-TANPA-PEGAWAI', '00000000');

        Livewire::actingAs($petugas)
            ->test(JurnalPerbaikanRiwayat::class)
            ->call('loadProperties')
            ->assertOk()
            ->assertSee('UJI-TANPA-PEGAWAI');
    }
}
