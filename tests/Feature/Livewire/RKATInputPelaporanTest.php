<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Keuangan\Modal\RKATInputPelaporan;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Models\Keuangan\RKAT\PemakaianAnggaranDetail;
use App\Settings\RKATSettings;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: reporting spending against a bidang's budget.
 *
 * A parent row plus an arbitrary number of detail lines, written in one
 * transaction. Unlike penetapan there is no calendar guard here, so the
 * permission check and the create/update split are the only things standing
 * between a petugas and the ledger.
 */
class RKATInputPelaporanTest extends TestCase
{
    /**
     * See RKATInputPenetapanTest::tearDown() for why the checks come off.
     */
    protected function tearDown(): void
    {
        $smc = DB::connection('mysql_smc');

        $smc->statement('set foreign_key_checks = 0');
        $smc->table('pemakaian_anggaran_detail')->delete();
        $smc->table('pemakaian_anggaran')->delete();
        $smc->table('anggaran_bidang')->delete();
        $smc->table('anggaran')->delete();
        $smc->table('bidang')->delete();
        $smc->statement('set foreign_key_checks = 1');

        parent::tearDown();
    }

    private function anggaranBidang(): AnggaranBidang
    {
        return AnggaranBidang::create([
            'anggaran_id'      => Anggaran::create(['nama' => 'Kategori Uji'])->id,
            'bidang_id'        => Bidang::create(['nama' => 'Bidang Uji'])->id,
            'tahun'            => app(RKATSettings::class)->tahun,
            'nominal_anggaran' => 10000000,
        ]);
    }

    /**
     * @test
     */
    public function refuses_a_report_from_a_petugas_without_the_permission(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $rkat = $this->anggaranBidang();

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->set('anggaranBidangId', $rkat->id)
            ->set('tglPakai', '2026-03-01')
            ->set('keterangan', 'Pembelian Uji')
            ->set('detail', [['keterangan' => 'Barang', 'nominal' => 250000]])
            ->call('create')
            ->assertDispatched('data-denied');

        $this->assertSame(0, PemakaianAnggaran::query()->count());
    }

    /**
     * @test
     *
     * The parent row and its detail lines are written together, so both are
     * checked rather than only that something was saved.
     */
    public function saves_a_report_with_its_detail_lines(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.create'], '99999901');
        $rkat = $this->anggaranBidang();

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->set('anggaranBidangId', $rkat->id)
            ->set('tglPakai', '2026-03-01')
            ->set('keterangan', 'Pembelian Uji')
            ->set('detail', [
                ['keterangan' => 'Barang A', 'nominal' => 250000],
                ['keterangan' => 'Barang B', 'nominal' => 125000],
            ])
            ->call('create')
            ->assertHasNoErrors()
            ->assertDispatched('data-saved');

        $tersimpan = PemakaianAnggaran::query()->sole();

        $this->assertSame('Pembelian Uji', $tersimpan->judul);
        $this->assertSame((int) $rkat->id, (int) $tersimpan->anggaran_bidang_id);
        $this->assertSame(2, PemakaianAnggaranDetail::query()
            ->where('pemakaian_anggaran_id', $tersimpan->id)
            ->count());
    }

    /**
     * @test
     *
     * A nominal is required on every line, so an empty one must not reach the
     * ledger as a zero.
     */
    public function rejects_a_detail_line_with_no_nominal(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.create'], '99999901');
        $rkat = $this->anggaranBidang();

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->set('anggaranBidangId', $rkat->id)
            ->set('tglPakai', '2026-03-01')
            ->set('keterangan', 'Pembelian Uji')
            ->set('detail', [['keterangan' => 'Barang', 'nominal' => '']])
            ->call('create')
            ->assertHasErrors('detail.0.nominal');

        $this->assertSame(0, PemakaianAnggaran::query()->count());
    }

    /**
     * @test
     *
     * update() delegates to create() when nothing is selected. It must stop
     * there: continuing runs PemakaianAnggaran::find(-1)->update(), which is a
     * fatal on null.
     */
    public function updating_a_report_that_does_not_exist_yet_creates_it_once(): void
    {
        $petugas = $this->petugasWithPermissions([
            'keuangan.rkat-pelaporan.create',
            'keuangan.rkat-pelaporan.update',
        ], '99999901');
        $rkat = $this->anggaranBidang();

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->set('anggaranBidangId', $rkat->id)
            ->set('tglPakai', '2026-03-01')
            ->set('keterangan', 'Pembelian Uji')
            ->set('detail', [['keterangan' => 'Barang', 'nominal' => 250000]])
            ->call('update')
            ->assertDispatched('data-saved');

        $this->assertSame(1, PemakaianAnggaran::query()->count());
    }

    /**
     * @test
     *
     * prepare() rebuilds the detail rows into the array shape the form binds to.
     */
    public function loading_an_existing_report_fills_the_detail_lines(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $rkat = $this->anggaranBidang();

        $pemakaian = PemakaianAnggaran::create([
            'judul'              => 'Pembelian Lama',
            'tgl_dipakai'        => '2026-02-01',
            'anggaran_bidang_id' => $rkat->id,
            'user_id'            => '99999901',
        ]);

        $pemakaian->detail()->createMany([
            ['keterangan' => 'Barang A', 'nominal' => 250000],
        ]);

        // rkat-pelaporan.blade.php nests the payload under "options" to match
        // prepare(array $options)'s parameter name; dispatched at the top level
        // it spreads as named arguments (pemakaianAnggaranId, tglPakai, ...),
        // none of which is named "options", and always threw. Named here too,
        // to match the corrected dispatch.
        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->dispatch('prepare', options: [
                'anggaranBidangId'    => $rkat->id,
                'pemakaianAnggaranId' => $pemakaian->id,
                'tglPakai'            => '2026-02-01',
                'keterangan'          => 'Pembelian Lama',
            ])
            ->assertSet('pemakaianAnggaranId', $pemakaian->id)
            ->assertSet('detail', [['keterangan' => 'Barang A', 'nominal' => 250000.0]]);
    }
}
