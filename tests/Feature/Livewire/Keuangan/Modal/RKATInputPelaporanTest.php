<?php

namespace Tests\Feature\Livewire\Keuangan\Modal;

use App\Livewire\Pages\Keuangan\Modal\RKATInputPelaporan;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Models\Keuangan\RKAT\PemakaianAnggaranDetail;
use App\Settings\RKATSettings;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
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

    private function anggaranBidang(?int $tahun = null): AnggaranBidang
    {
        return AnggaranBidang::create([
            'anggaran_id'      => Anggaran::create(['nama' => 'Kategori Uji'])->id,
            'bidang_id'        => Bidang::create(['nama' => 'Bidang Uji'])->id,
            'tahun'            => $tahun ?? app(RKATSettings::class)->tahun,
            'nominal_anggaran' => 10000000,
        ]);
    }

    /**
     * @test
     *
     * The Tahun RKAT is 2026 in the test schema. It limits which year
     * Penetapan RKAT is set for, not which year spending may be recorded
     * against: the form offers the Penetapan of whatever year the tanggal pakai
     * falls in.
     */
    public function offers_the_penetapan_of_the_year_the_tanggal_pakai_falls_in(): void
    {
        $tahunLalu = $this->anggaranBidang(2025);
        $tahunIni = $this->anggaranBidang(2026);

        $test = Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(RKATInputPelaporan::class)
            ->set('tglPakai', '2026-01-10');

        $this->assertSame([(int) $tahunIni->id], array_keys($test->get('dataRKATPerBidang')->all()));

        $test->set('tglPakai', '2025-12-20');

        $this->assertSame([(int) $tahunLalu->id], array_keys($test->get('dataRKATPerBidang')->all()));
    }

    /**
     * @test
     */
    public function records_spending_against_last_years_penetapan(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.create'], '99999901');
        $tahunLalu = $this->anggaranBidang(2025);

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->set('tglPakai', '2025-12-20')
            ->set('anggaranBidangId', $tahunLalu->id)
            ->set('keterangan', 'Tagihan Desember')
            ->set('detail', [['keterangan' => 'Barang', 'nominal' => 1000]])
            ->call('create')
            ->assertHasNoErrors()
            ->assertDispatched('data-saved');

        $this->assertSame((int) $tahunLalu->id, (int) PemakaianAnggaran::query()->sole()->anggaran_bidang_id);
    }

    /**
     * @test
     */
    public function moving_the_tanggal_pakai_to_another_year_clears_the_chosen_penetapan(): void
    {
        $tahunIni = $this->anggaranBidang(2026);

        Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(RKATInputPelaporan::class)
            ->set('tglPakai', '2026-03-01')
            ->set('anggaranBidangId', $tahunIni->id)
            ->set('tglPakai', '2026-04-15')
            ->assertSet('anggaranBidangId', $tahunIni->id)
            ->set('tglPakai', '2025-12-20')
            ->assertSet('anggaranBidangId', -1);
    }

    /**
     * @test
     *
     * The form clears a choice from the wrong year, but the rule has to hold on
     * save as well: here the Penetapan is chosen after the date.
     */
    public function refuses_a_tanggal_pakai_outside_the_year_of_the_chosen_penetapan(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.create'], '99999901');
        $tahunIni = $this->anggaranBidang(2026);

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->set('tglPakai', '2025-12-20')
            ->set('anggaranBidangId', $tahunIni->id)
            ->set('keterangan', 'Pembelian Uji')
            ->set('detail', [['keterangan' => 'Barang', 'nominal' => 1000]])
            ->call('create')
            ->assertHasErrors('tglPakai')
            ->assertNotDispatched('data-saved');

        $this->assertSame(0, PemakaianAnggaran::query()->count());
    }

    /**
     * @test
     */
    public function refuses_an_edit_that_moves_the_tanggal_pakai_out_of_the_penetapan_year(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.update'], '99999901');
        $rkat = $this->anggaranBidang(2026);
        [$pemakaian, $options] = $this->laporanTersimpan($rkat);

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->dispatch('prepare', options: $options)
            ->set('tglPakai', '2025-12-20')
            ->set('anggaranBidangId', $rkat->id)
            ->call('create')
            ->assertHasErrors('tglPakai')
            ->assertNotDispatched('data-saved');

        $this->assertSame('2026-02-01', carbon($pemakaian->refresh()->tgl_dipakai)->toDateString());
    }

    /**
     * @test
     *
     * Rincian Pemakaian attached from a file are saved by a queued job, which
     * must not be queued for a date outside the Penetapan's year.
     */
    public function refuses_to_queue_an_import_outside_the_year_of_the_chosen_penetapan(): void
    {
        Queue::fake();

        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.create'], '99999901');
        $tahunIni = $this->anggaranBidang(2026);

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->set('tglPakai', '2025-12-20')
            ->set('anggaranBidangId', $tahunIni->id)
            ->set('keterangan', 'Pembelian Uji')
            ->set('fileImport', UploadedFile::fake()->create('rincian.xlsx', 10))
            ->call('create')
            ->assertHasErrors('tglPakai');

        Queue::assertNothingPushed();
    }

    /**
     * @test
     *
     * Opening a Pemakaian from last year, after the Tahun RKAT has moved on,
     * must still offer its own Penetapan so the dropdown can show it.
     */
    public function opening_last_years_pemakaian_offers_its_own_penetapan(): void
    {
        $tahunLalu = $this->anggaranBidang(2025);
        $this->anggaranBidang(2026);

        $pemakaian = PemakaianAnggaran::create([
            'judul'              => 'Pembelian Lama',
            'tgl_dipakai'        => '2025-11-03',
            'anggaran_bidang_id' => $tahunLalu->id,
            'user_id'            => '99999901',
        ]);

        $test = Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(RKATInputPelaporan::class)
            ->dispatch('prepare', options: [
                'anggaranBidangId'    => $tahunLalu->id,
                'pemakaianAnggaranId' => $pemakaian->id,
                'tglPakai'            => '2025-11-03',
                'keterangan'          => 'Pembelian Lama',
            ])
            ->assertSet('anggaranBidangId', $tahunLalu->id);

        $this->assertSame([(int) $tahunLalu->id], array_keys($test->get('dataRKATPerBidang')->all()));
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

    /**
     * @test
     *
     * "Laporan Baru" only has a bare data-toggle="modal" button behind it,
     * with no action to reset the form - unlike a row click, which reaches
     * prepare() via JS. rkat-input-pelaporan.blade.php's shown.bs.modal
     * handler dispatches prepare with no options at all in that case, which
     * needs its own empty-array branch: reading tglPakai/keterangan out of an
     * empty array left them null instead of defaultValues()'s actual
     * defaults, and detail ended up [] instead of one blank row.
     */
    public function reopening_for_a_new_report_resets_the_form(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $rkat = $this->anggaranBidang();

        $pemakaian = PemakaianAnggaran::create([
            'judul'              => 'Pembelian Lama',
            'tgl_dipakai'        => '2026-02-01',
            'anggaran_bidang_id' => $rkat->id,
            'user_id'            => '99999901',
        ]);
        $pemakaian->detail()->createMany([['keterangan' => 'Barang A', 'nominal' => 250000]]);

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->dispatch('prepare', options: [
                'anggaranBidangId'    => $rkat->id,
                'pemakaianAnggaranId' => $pemakaian->id,
                'tglPakai'            => '2026-02-01',
                'keterangan'          => 'Pembelian Lama',
            ])
            ->assertSet('pemakaianAnggaranId', $pemakaian->id)
            ->dispatch('prepare')
            ->assertSet('anggaranBidangId', -1)
            ->assertSet('pemakaianAnggaranId', -1)
            ->assertSet('tglPakai', now()->toDateString())
            ->assertSet('keterangan', '')
            ->assertSet('detail', [['keterangan' => '', 'nominal' => 0]]);
    }

    /**
     * @test
     *
     * update() delegates through isUpdating() on pemakaianAnggaranId. Before
     * the dispatch above ran on every "Laporan Baru" open, a stale
     * pemakaianAnggaranId left over from editing $pemakaian meant this call
     * silently overwrote it instead of creating a new report.
     */
    public function creating_after_editing_another_report_does_not_touch_it(): void
    {
        $petugas = $this->petugasWithPermissions([
            'keuangan.rkat-pelaporan.create',
            'keuangan.rkat-pelaporan.update',
        ], '99999901');
        $rkat = $this->anggaranBidang();

        $pemakaian = PemakaianAnggaran::create([
            'judul'              => 'Pembelian Lama',
            'tgl_dipakai'        => '2026-02-01',
            'anggaran_bidang_id' => $rkat->id,
            'user_id'            => '99999901',
        ]);
        $pemakaian->detail()->createMany([['keterangan' => 'Barang A', 'nominal' => 250000]]);

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->dispatch('prepare', options: [
                'anggaranBidangId'    => $rkat->id,
                'pemakaianAnggaranId' => $pemakaian->id,
                'tglPakai'            => '2026-02-01',
                'keterangan'          => 'Pembelian Lama',
            ])
            ->dispatch('prepare')
            ->set('anggaranBidangId', $rkat->id)
            ->set('tglPakai', '2026-03-01')
            ->set('keterangan', 'Pembelian Baru')
            ->set('detail', [['keterangan' => 'Barang B', 'nominal' => 125000]])
            ->call('create')
            ->assertDispatched('data-saved');

        $pemakaian->refresh();

        $this->assertSame('Pembelian Lama', $pemakaian->judul);
        $this->assertSame(2, PemakaianAnggaran::query()->count());
    }

    /**
     * An existing report with one detail line, and the options array the row
     * click hands to prepare() for it.
     *
     * @return array{0: PemakaianAnggaran, 1: array<string, mixed>}
     */
    private function laporanTersimpan(AnggaranBidang $rkat, array $detail = [['keterangan' => 'Barang A', 'nominal' => 250000]]): array
    {
        $pemakaian = PemakaianAnggaran::create([
            'judul'              => 'Pembelian Lama',
            'tgl_dipakai'        => '2026-02-01',
            'anggaran_bidang_id' => $rkat->id,
            'user_id'            => '99999901',
        ]);

        if ($detail !== []) {
            $pemakaian->detail()->createMany($detail);
        }

        return [$pemakaian, [
            'anggaranBidangId'    => $rkat->id,
            'pemakaianAnggaranId' => $pemakaian->id,
            'tglPakai'            => '2026-02-01',
            'keterangan'          => 'Pembelian Lama',
        ]];
    }

    /**
     * @test
     *
     * Updating rewrites the detail lines wholesale — deletes them all, then
     * inserts the form's. The old lines must be gone, not added to.
     */
    public function updating_a_report_replaces_its_detail_lines(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.update'], '99999901');
        [$pemakaian, $options] = $this->laporanTersimpan($this->anggaranBidang());

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->dispatch('prepare', options: $options)
            ->set('keterangan', 'Pembelian Diubah')
            ->set('detail', [
                ['keterangan' => 'Barang C', 'nominal' => 100000],
                ['keterangan' => 'Barang D', 'nominal' => 50000],
            ])
            ->call('create')
            ->assertHasNoErrors()
            ->assertDispatched('data-saved');

        $this->assertSame('Pembelian Diubah', $pemakaian->refresh()->judul);
        $this->assertSame(
            ['Barang C', 'Barang D'],
            $pemakaian->detail()->orderBy('keterangan')->pluck('keterangan')->all()
        );
        $this->assertEqualsWithDelta(150000, $pemakaian->detail()->sum('nominal'), 0.001);
    }

    /**
     * @test
     */
    public function refuses_an_update_without_the_update_permission(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.create'], '99999901');
        [$pemakaian, $options] = $this->laporanTersimpan($this->anggaranBidang());

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->dispatch('prepare', options: $options)
            ->set('keterangan', 'Pembelian Diubah')
            ->call('create')
            ->assertDispatched('data-denied');

        $this->assertSame('Pembelian Lama', $pemakaian->refresh()->judul);
        $this->assertSame(1, $pemakaian->detail()->count());
    }

    /**
     * @test
     */
    public function refuses_a_delete_without_the_delete_permission(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.update'], '99999901');
        [, $options] = $this->laporanTersimpan($this->anggaranBidang(), []);

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->dispatch('prepare', options: $options)
            ->call('delete')
            ->assertDispatched('data-denied');

        $this->assertSame(1, PemakaianAnggaran::query()->count());
    }

    /**
     * @test
     */
    public function deletes_a_report_that_has_no_detail_lines(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.delete'], '99999901');
        [, $options] = $this->laporanTersimpan($this->anggaranBidang(), []);

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->dispatch('prepare', options: $options)
            ->call('delete')
            ->assertDispatched('data-saved');

        $this->assertSame(0, PemakaianAnggaran::query()->count());
    }

    /**
     * DEFECT, recorded rather than asserted as correct.
     *
     * pemakaian_anggaran_detail.pemakaian_anggaran_id is a RESTRICT foreign key
     * (the migration's plain ->constrained(), and the same rule on the dev smc
     * schema), and neither delete() nor the model removes the detail lines
     * first. Every real report has at least one line, so deleting a report from
     * this modal fails on the constraint, uncaught, and the row stays.
     *
     * Deleting $pemakaianAnggaran->detail() before the parent, inside a
     * transaction, fixes it. Flip this test to assert the report and its lines
     * are gone when that lands.
     *
     * @test
     */
    public function deleting_a_report_with_detail_lines_currently_fails_on_the_foreign_key(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.delete'], '99999901');
        [, $options] = $this->laporanTersimpan($this->anggaranBidang());

        try {
            Livewire::actingAs($petugas)
                ->test(RKATInputPelaporan::class)
                ->dispatch('prepare', options: $options)
                ->call('delete');

            $this->fail('Menghapus laporan RKAT yang punya rincian ternyata berhasil; balik test ini.');
        } catch (QueryException $e) {
            $this->assertSame('23000', $e->getCode());
        }

        $this->assertSame(1, PemakaianAnggaran::query()->count());
    }

    /**
     * @test
     */
    public function add_detail_appends_a_blank_line(): void
    {
        Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(RKATInputPelaporan::class)
            ->call('addDetail')
            ->assertCount('detail', 2)
            ->assertSet('detail.1', ['keterangan' => '', 'nominal' => 0]);
    }

    /**
     * @test
     *
     * removeDetail() unsets by index, leaving a gap in the keys. The lines that
     * are left must still be the ones saved.
     */
    public function a_removed_detail_line_is_not_saved(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.create'], '99999901');
        $rkat = $this->anggaranBidang();

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->set('anggaranBidangId', $rkat->id)
            ->set('tglPakai', '2026-03-01')
            ->set('keterangan', 'Pembelian Uji')
            ->set('detail', [
                ['keterangan' => 'Tetap A', 'nominal' => 1000],
                ['keterangan' => 'Dibuang', 'nominal' => 2000],
                ['keterangan' => 'Tetap B', 'nominal' => 3000],
            ])
            ->call('removeDetail', 1)
            ->call('create')
            ->assertHasNoErrors()
            ->assertDispatched('data-saved');

        $this->assertSame(
            ['Tetap A', 'Tetap B'],
            PemakaianAnggaranDetail::query()->orderBy('keterangan')->pluck('keterangan')->all()
        );
    }

    /**
     * @test
     *
     * A null keterangan on a line gets past validation (the rule allows it) and
     * fails on the NOT NULL column, which is the simplest way to make the save
     * itself throw. The failure used to arrive in a green success banner.
     */
    public function a_failed_save_is_announced_as_an_error(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.create'], '99999901');
        $rkat = $this->anggaranBidang();

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->set('anggaranBidangId', $rkat->id)
            ->set('tglPakai', '2026-03-01')
            ->set('keterangan', 'Pembelian Uji')
            ->set('detail', [['keterangan' => null, 'nominal' => 1000]])
            ->call('create')
            ->assertDispatched('data-failed')
            ->assertNotDispatched('flash.success')
            ->assertDispatched(
                'flash.error',
                fn (string $event, array $params): bool => str_contains($params[0], 'kegagalan')
            )
            ->assertSet('keterangan', 'Pembelian Uji');

        $this->assertSame(0, PemakaianAnggaran::query()->count());
    }

    /**
     * @test
     *
     * Updating removes every Rincian Pemakaian before writing the form's. If the
     * write fails after the removal, the Pemakaian must keep the Rincian it had,
     * not be left with none.
     */
    public function a_failed_update_keeps_the_existing_lines_and_is_announced_as_an_error(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-pelaporan.update'], '99999901');
        [$pemakaian, $options] = $this->laporanTersimpan($this->anggaranBidang());

        Livewire::actingAs($petugas)
            ->test(RKATInputPelaporan::class)
            ->dispatch('prepare', options: $options)
            ->set('keterangan', 'Pembelian Diubah')
            ->set('detail', [['keterangan' => null, 'nominal' => 1000]])
            ->call('create')
            ->assertDispatched('data-failed')
            ->assertNotDispatched('data-saved')
            ->assertNotDispatched('flash.success')
            ->assertDispatched('flash.error');

        $this->assertSame('Pembelian Lama', $pemakaian->refresh()->judul);
        $this->assertSame(['Barang A'], $pemakaian->detail()->pluck('keterangan')->all());
    }
}
