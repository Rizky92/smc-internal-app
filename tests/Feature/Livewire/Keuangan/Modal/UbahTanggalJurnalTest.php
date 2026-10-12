<?php

namespace Tests\Feature\Livewire\Keuangan\Modal;

use App\Livewire\Pages\Keuangan\Modal\UbahTanggalJurnal;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\JurnalBackup;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: changing a jurnal's date, with a backup/restore trail.
 *
 * The one business rule here that isn't just a permission check: the jurnal
 * recorded last on a given date can't be redated - identified by the highest
 * right(no_jurnal, 6) for that date - because reordering the newest entry on
 * a date would misrepresent history for everyone else booked that day.
 * Confirmed by seeding two entries on the same date and redating the earlier
 * one (allowed) vs. the later one (refused).
 */
class UbahTanggalJurnalTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::connection('mysql_sik')->table('jurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        DB::connection('mysql_smc')->table('jurnal_backup')->where('no_jurnal', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function jurnal(string $noJurnal, string $tanggal): Jurnal
    {
        return Jurnal::create([
            'no_jurnal'  => $noJurnal,
            'no_bukti'   => 'BUKTI-'.$noJurnal,
            'keterangan' => 'Jurnal '.$noJurnal,
            'jenis'      => 'U',
            'tgl_jurnal' => $tanggal,
            'jam_jurnal' => '09:00:00',
        ]);
    }

    /**
     * @test
     *
     * prepareJurnal() is fed by jurnal-perbaikan.blade.php's
     * wire:click.prevent="$dispatch('utj.prepare', { data: {...} })" - a
     * regular action call, which (unlike Livewire.dispatch()) passes its full
     * payload through safely. Named here to match.
     */
    public function preparing_a_jurnal_fills_the_form(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(UbahTanggalJurnal::class)
            ->dispatch('utj.prepare', data: [
                'noJurnal'   => 'UJI-JR-000001',
                'noBukti'    => 'BUKTI-UJI-JR-000001',
                'keterangan' => 'Uji Jurnal',
                'tglJurnal'  => '2026-03-05',
                'jamJurnal'  => '09:00:00',
            ])
            ->assertSet('noJurnal', 'UJI-JR-000001')
            ->assertSet('tglJurnalLama', '2026-03-05')
            ->assertSet('tglJurnalBaru', '2026-03-05');
    }

    /**
     * @test
     */
    public function refuses_to_change_the_date_without_permission(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $jurnal = $this->jurnal('UJI-JR-000001', '2026-03-05');

        Livewire::actingAs($petugas)
            ->test(UbahTanggalJurnal::class)
            ->set('noJurnal', $jurnal->no_jurnal)
            ->set('tglJurnalLama', '2026-03-05')
            ->set('tglJurnalBaru', '2026-03-10')
            ->call('updateTglJurnal');

        $this->assertSame('2026-03-05', Jurnal::find($jurnal->no_jurnal)->tgl_jurnal);
        $this->assertSame(0, JurnalBackup::query()->where('no_jurnal', $jurnal->no_jurnal)->count());
    }

    /**
     * @test
     *
     * UJI-JR-000002 is the entry with the higher right(no_jurnal, 6) for
     * 2026-03-05, so it's "the last one recorded" and redating it is refused.
     */
    public function refuses_to_change_the_last_entry_recorded_on_its_date(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.jurnal-perbaikan.ubah-tanggal'], '99999901');
        $this->jurnal('UJI-JR-000001', '2026-03-05');
        $terbaru = $this->jurnal('UJI-JR-000002', '2026-03-05');

        Livewire::actingAs($petugas)
            ->test(UbahTanggalJurnal::class)
            ->set('noJurnal', $terbaru->no_jurnal)
            ->set('tglJurnalLama', '2026-03-05')
            ->set('tglJurnalBaru', '2026-03-10')
            ->call('updateTglJurnal');

        $this->assertSame('2026-03-05', Jurnal::find($terbaru->no_jurnal)->tgl_jurnal);
        $this->assertSame(0, JurnalBackup::query()->where('no_jurnal', $terbaru->no_jurnal)->count());
    }

    /**
     * @test
     *
     * UJI-JR-000001 is NOT the last entry on its date (UJI-JR-000002 is), so
     * redating it is allowed and leaves a JurnalBackup trail behind.
     */
    public function changes_the_date_of_an_earlier_entry_and_backs_it_up(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.jurnal-perbaikan.ubah-tanggal'], '99999901');
        $lama = $this->jurnal('UJI-JR-000001', '2026-03-05');
        $this->jurnal('UJI-JR-000002', '2026-03-05');

        Livewire::actingAs($petugas)
            ->test(UbahTanggalJurnal::class)
            ->set('noJurnal', $lama->no_jurnal)
            ->set('tglJurnalLama', '2026-03-05')
            ->set('tglJurnalBaru', '2026-03-10')
            ->call('updateTglJurnal')
            ->assertDispatched('jurnal-updated');

        $this->assertSame('2026-03-10', Jurnal::find($lama->no_jurnal)->tgl_jurnal);

        $backup = JurnalBackup::query()->where('no_jurnal', $lama->no_jurnal)->sole();
        $this->assertSame('2026-03-05', $backup->tgl_jurnal_asli);
        $this->assertSame('99999901', $backup->nip);
    }

    /**
     * @test
     *
     * restoreTglJurnal()'s permission guard called $this->flasError() - a
     * typo missing the "h" - which doesn't exist on FlashComponent and threw
     * a fatal Error instead of showing the intended message. A petugas
     * without the permission clicking "Restore" crashed the request instead
     * of just seeing a flash telling them no.
     */
    public function refusing_to_restore_a_date_does_not_crash(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $jurnal = $this->jurnal('UJI-JR-000001', '2026-03-10');
        $backup = JurnalBackup::create([
            'no_jurnal'         => $jurnal->no_jurnal,
            'tgl_jurnal_asli'   => '2026-03-05',
            'tgl_jurnal_diubah' => '2026-03-10',
            'nip'               => '99999901',
        ]);

        Livewire::actingAs($petugas)
            ->test(UbahTanggalJurnal::class)
            ->set('noJurnal', $jurnal->no_jurnal)
            ->call('restoreTglJurnal', $backup->id);

        $this->assertSame('2026-03-10', Jurnal::find($jurnal->no_jurnal)->tgl_jurnal);
        $this->assertNotNull(JurnalBackup::find($backup->id));
    }

    /**
     * @test
     */
    public function restores_a_jurnal_to_its_original_date(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.jurnal-perbaikan.ubah-tanggal'], '99999901');
        $jurnal = $this->jurnal('UJI-JR-000001', '2026-03-10');
        $backup = JurnalBackup::create([
            'no_jurnal'         => $jurnal->no_jurnal,
            'tgl_jurnal_asli'   => '2026-03-05',
            'tgl_jurnal_diubah' => '2026-03-10',
            'nip'               => '99999901',
        ]);

        Livewire::actingAs($petugas)
            ->test(UbahTanggalJurnal::class)
            ->set('noJurnal', $jurnal->no_jurnal)
            ->call('restoreTglJurnal', $backup->id)
            ->assertDispatched('jurnal-restored');

        $this->assertSame('2026-03-05', Jurnal::find($jurnal->no_jurnal)->tgl_jurnal);
        $this->assertNull(JurnalBackup::find($backup->id));
    }

    /**
     * @test
     */
    public function hides_the_backup_history_without_permission(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $jurnal = $this->jurnal('UJI-JR-000001', '2026-03-10');
        JurnalBackup::create([
            'no_jurnal' => $jurnal->no_jurnal, 'tgl_jurnal_asli' => '2026-03-05',
            'tgl_jurnal_diubah' => '2026-03-10', 'nip' => '99999901',
        ]);

        Livewire::actingAs($petugas)
            ->test(UbahTanggalJurnal::class)
            ->set('noJurnal', $jurnal->no_jurnal)
            ->assertDontSee('2026-03-05');
    }

    /**
     * @test
     */
    public function shows_the_backup_history_with_permission(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.riwayat-jurnal-perbaikan.read'], '99999901');
        $jurnal = $this->jurnal('UJI-JR-000001', '2026-03-10');
        JurnalBackup::create([
            'no_jurnal' => $jurnal->no_jurnal, 'tgl_jurnal_asli' => '2026-03-05',
            'tgl_jurnal_diubah' => '2026-03-10', 'nip' => '99999901',
        ]);

        Livewire::actingAs($petugas)
            ->test(UbahTanggalJurnal::class)
            ->set('noJurnal', $jurnal->no_jurnal)
            ->assertSee('2026-03-05');
    }
}
