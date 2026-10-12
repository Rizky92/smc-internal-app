<?php

namespace Tests\Feature\Livewire\Keuangan\Modal;

use App\Livewire\Pages\Keuangan\Modal\RKATInputPenetapan;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Settings\RKATSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: setting a bidang's budget for the year.
 *
 * A financial write path, and the only one in the application guarded by a
 * calendar as well as a permission: penetapan is refused outside the window in
 * RKATSettings, whoever is asking. Both guards are worth pinning, because a
 * migration that quietly loses one of them costs money rather than a 500.
 *
 * Tests travel into the configured window rather than rewriting the settings, so
 * they assert against the period the hospital actually configured.
 */
class RKATInputPenetapanTest extends TestCase
{
    /**
     * Cleanup runs with foreign key checks off, which needs explaining.
     *
     * On this MariaDB (10.4.32), deleting a row from `anggaran` is refused by
     * anggaran_bidang_anggaran_id_foreign even when anggaran_bidang is provably
     * empty — COUNT(*) returns 0 through both the primary key and the unique
     * index, and CHECK TABLE reports OK. Ruled out: stale InnoDB dictionary
     * entries, an orphaned open transaction, the shape of the covering index
     * (adding a dedicated one changes nothing), and the three-level chain into
     * pemakaian_anggaran. A synthetic schema of the same shape does not
     * reproduce it. The cause is still unknown.
     *
     * Disabling the checks is safe here — this is a dedicated test schema and the
     * child table is empty — but the same delete is what RKATInputKategori
     * performs, so if it behaves this way on the production server then removing
     * an RKAT category fails there too. Worth checking against production's
     * actual MariaDB version.
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

    private function insidePeriod(): Carbon
    {
        return app(RKATSettings::class)->tgl_penetapan_awal->copy()->addDay();
    }

    private function outsidePeriod(): Carbon
    {
        return app(RKATSettings::class)->tgl_penetapan_akhir->copy()->addYear();
    }

    private function kategori(): Anggaran
    {
        return Anggaran::create(['nama' => 'Kategori Uji']);
    }

    private function bidang(): Bidang
    {
        return Bidang::create(['nama' => 'Bidang Uji']);
    }

    /**
     * @test
     *
     * The calendar guard runs before validation and before the write, so a
     * petugas who holds the permission is still refused out of season.
     */
    public function refuses_a_penetapan_outside_the_configured_period(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.create'], '99999901');
        $anggaran = $this->kategori();
        $bidang = $this->bidang();

        $this->travelTo($this->outsidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->set('anggaranId', $anggaran->id)
            ->set('bidangId', $bidang->id)
            ->set('nominalAnggaran', 1000000)
            ->call('create')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-saved');

        $this->assertSame(0, AnggaranBidang::query()->count());
    }

    /**
     * @test
     */
    public function refuses_a_penetapan_to_a_petugas_without_the_permission(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $anggaran = $this->kategori();
        $bidang = $this->bidang();

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->set('anggaranId', $anggaran->id)
            ->set('bidangId', $bidang->id)
            ->set('nominalAnggaran', 1000000)
            ->call('create')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-saved');

        $this->assertSame(0, AnggaranBidang::query()->count());
    }

    /**
     * @test
     *
     * The year written is the one in RKATSettings, not the year the clock says,
     * which is the whole point of the setting.
     */
    public function saves_a_penetapan_inside_the_period(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.create'], '99999901');
        $anggaran = $this->kategori();
        $bidang = $this->bidang();
        $tahun = app(RKATSettings::class)->tahun;

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->set('anggaranId', $anggaran->id)
            ->set('bidangId', $bidang->id)
            ->set('nominalAnggaran', 1234567.89)
            ->call('create')
            ->assertHasNoErrors()
            ->assertDispatched('data-saved');

        $tersimpan = AnggaranBidang::query()->sole();

        $this->assertSame((int) $anggaran->id, (int) $tersimpan->anggaran_id);
        $this->assertSame((int) $bidang->id, (int) $tersimpan->bidang_id);
        $this->assertSame((int) $tahun, (int) $tersimpan->tahun);
        $this->assertEquals(1234567.89, $tersimpan->nominal_anggaran);
    }

    /**
     * @test
     *
     * The page still offers superadmin its actions outside the Periode
     * Penetapan, but the writes themselves hold superadmin to the period like
     * everyone else, and say why.
     */
    public function superadmin_is_told_the_period_has_passed_on_every_write(): void
    {
        $superadmin = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');
        $existing = $this->penetapanTersimpan();

        $this->travelTo($this->outsidePeriod());

        Livewire::actingAs($superadmin)
            ->test(RKATInputPenetapan::class)
            ->set('anggaranId', $this->kategori()->id)
            ->set('bidangId', $this->bidang()->id)
            ->set('nominalAnggaran', 500000)
            ->call('create')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-saved')
            ->assertSee('diluar periode');

        Livewire::actingAs($superadmin)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->set('nominalAnggaran', 750000)
            ->call('create')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-saved')
            ->assertSee('melewati periode');

        Livewire::actingAs($superadmin)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->call('delete')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-deleted')
            ->assertSee('melewati periode');

        $this->assertSame(1, AnggaranBidang::query()->count());
        $this->assertSame(500000, (int) $existing->refresh()->nominal_anggaran);
    }

    /**
     * @test
     */
    public function rejects_a_penetapan_with_no_nominal(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.create'], '99999901');
        $anggaran = $this->kategori();
        $bidang = $this->bidang();

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->set('anggaranId', $anggaran->id)
            ->set('bidangId', $bidang->id)
            ->set('nominalAnggaran', '')
            ->call('create')
            ->assertHasErrors('nominalAnggaran');

        $this->assertSame(0, AnggaranBidang::query()->count());
    }

    /**
     * @test
     *
     * Selecting a penetapan and deleting it should remove it.
     */
    public function deletes_the_selected_penetapan(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.delete'], '99999901');
        $anggaran = $this->kategori();
        $bidang = $this->bidang();

        $existing = AnggaranBidang::create([
            'anggaran_id'      => $anggaran->id,
            'bidang_id'        => $bidang->id,
            'tahun'            => app(RKATSettings::class)->tahun,
            'nominal_anggaran' => 500000,
        ]);

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->call('delete')
            ->assertDispatched('data-deleted')
            ->assertNotDispatched('data-denied');

        $this->assertSame(0, AnggaranBidang::query()->count());
    }

    /**
     * @test
     *
     * A stale row id reaches prepare() whenever the table is open while somebody
     * else deletes the row. Falling back to create mode is recoverable; a fatal
     * on null is not.
     */
    public function preparing_a_penetapan_that_no_longer_exists_falls_back_to_create_mode(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', 999999)
            ->assertSet('anggaranBidangId', -1);
    }

    /**
     * @test
     *
     * prepare() is how the table hands a row to this modal for editing.
     */
    public function loading_an_existing_penetapan_fills_the_form(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $anggaran = $this->kategori();
        $bidang = $this->bidang();

        $existing = AnggaranBidang::create([
            'anggaran_id'      => $anggaran->id,
            'bidang_id'        => $bidang->id,
            'tahun'            => app(RKATSettings::class)->tahun,
            'nominal_anggaran' => 500000,
        ]);

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->assertSet('anggaranBidangId', $existing->id)
            ->assertSet('anggaranId', $anggaran->id)
            ->assertSet('bidangId', $bidang->id);
    }

    /**
     * @test
     *
     * "Anggaran Baru" only has a bare data-toggle="modal" button behind it,
     * with no action to reset the form - unlike a row click, which reaches
     * prepare() via JS. rkat-input-penetapan.blade.php's shown.bs.modal
     * handler dispatches prepare with no id at all in that case, which this
     * component already handles via its existing "id not found" fallback
     * (AnggaranBidang::find(-1) is null) - this pins that the reset actually
     * happens when the button, not just a stale id, triggers it.
     */
    public function reopening_for_a_new_penetapan_resets_the_form(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $anggaran = $this->kategori();
        $bidang = $this->bidang();

        $existing = AnggaranBidang::create([
            'anggaran_id'      => $anggaran->id,
            'bidang_id'        => $bidang->id,
            'tahun'            => app(RKATSettings::class)->tahun,
            'nominal_anggaran' => 500000,
        ]);

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->assertSet('anggaranBidangId', $existing->id)
            ->dispatch('prepare')
            ->assertSet('anggaranBidangId', -1)
            ->assertSet('anggaranId', -1)
            ->assertSet('bidangId', -1);
    }

    /**
     * @test
     *
     * update() delegates through isUpdating() on anggaranBidangId. Before the
     * dispatch above ran on every "Anggaran Baru" open, a stale
     * anggaranBidangId left over from editing $existing meant this call
     * silently overwrote it instead of creating a new penetapan.
     */
    public function creating_after_editing_another_penetapan_does_not_touch_it(): void
    {
        $petugas = $this->petugasWithPermissions([
            'keuangan.rkat-penetapan.create',
            'keuangan.rkat-penetapan.update',
        ], '99999901');
        $anggaran = $this->kategori();
        $bidang = $this->bidang();
        $anggaranBaru = $this->kategori();
        $bidangBaru = $this->bidang();

        $existing = AnggaranBidang::create([
            'anggaran_id'      => $anggaran->id,
            'bidang_id'        => $bidang->id,
            'tahun'            => app(RKATSettings::class)->tahun,
            'nominal_anggaran' => 500000,
        ]);

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->dispatch('prepare')
            ->set('anggaranId', $anggaranBaru->id)
            ->set('bidangId', $bidangBaru->id)
            ->set('nominalAnggaran', 750000)
            ->call('create')
            ->assertDispatched('data-saved');

        $existing->refresh();

        $this->assertSame((int) $anggaran->id, (int) $existing->anggaran_id);
        $this->assertSame(500000, (int) $existing->nominal_anggaran);
        $this->assertSame(2, AnggaranBidang::query()->count());
    }

    private function penetapanTersimpan(int $nominal = 500000): AnggaranBidang
    {
        return AnggaranBidang::create([
            'anggaran_id'      => $this->kategori()->id,
            'bidang_id'        => $this->bidang()->id,
            'tahun'            => app(RKATSettings::class)->tahun,
            'nominal_anggaran' => $nominal,
        ]);
    }

    /**
     * @test
     */
    public function updates_the_nominal_of_the_selected_penetapan(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.update'], '99999901');
        $existing = $this->penetapanTersimpan();

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->set('nominalAnggaran', 875000.5)
            ->call('create')
            ->assertHasNoErrors()
            ->assertDispatched('data-saved')
            // A successful update resets the form so the next open starts clean.
            ->assertSet('anggaranBidangId', -1);

        $this->assertEquals(875000.5, $existing->refresh()->nominal_anggaran);
        $this->assertSame(1, AnggaranBidang::query()->count());
    }

    private function pemakaianUntuk(AnggaranBidang $penetapan): void
    {
        PemakaianAnggaran::create([
            'judul'              => 'Pembelian Uji',
            'tgl_dipakai'        => '2026-03-01',
            'anggaran_bidang_id' => $penetapan->id,
            'user_id'            => '99999901',
        ]);
    }

    /**
     * @test
     *
     * Changing the Kategori Anggaran of a Penetapan RKAT that has Pemakaian
     * Anggaran would move all of that spending to another category.
     */
    public function refuses_a_new_kategori_for_a_penetapan_that_has_spending_reported(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.update'], '99999901');
        $existing = $this->penetapanTersimpan();
        $this->pemakaianUntuk($existing);
        $kategoriLain = $this->kategori();

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->set('anggaranId', (string) $kategoriLain->id)
            ->call('create')
            ->assertHasErrors('anggaranId')
            ->assertNotDispatched('data-saved');

        $this->assertNotSame((int) $kategoriLain->id, (int) $existing->refresh()->anggaran_id);
    }

    /**
     * @test
     */
    public function refuses_a_new_bidang_for_a_penetapan_that_has_spending_reported(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.update'], '99999901');
        $existing = $this->penetapanTersimpan();
        $this->pemakaianUntuk($existing);
        $bidangLain = $this->bidang();

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->set('bidangId', (string) $bidangLain->id)
            ->call('create')
            ->assertHasErrors('bidangId')
            ->assertNotDispatched('data-saved');

        $this->assertNotSame((int) $bidangLain->id, (int) $existing->refresh()->bidang_id);
    }

    /**
     * @test
     *
     * Only the Kategori Anggaran and Bidang are fixed. The amount can still be
     * revised, even below what has already been spent.
     */
    public function still_revises_the_nominal_of_a_penetapan_that_has_spending_reported(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.update'], '99999901');
        $existing = $this->penetapanTersimpan();
        $this->pemakaianUntuk($existing);

        $this->travelTo($this->insidePeriod());

        $test = Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id);

        $html = $test->html();
        $this->assertMatchesRegularExpression('/<select(?=[^>]*id="anggaran-id")(?=[^>]*\sdisabled)[^>]*>/', $html);
        $this->assertMatchesRegularExpression('/<select(?=[^>]*id="bidang-id")(?=[^>]*\sdisabled)[^>]*>/', $html);

        $test->set('nominalAnggaran', 1000)
            ->call('create')
            ->assertHasNoErrors()
            ->assertDispatched('data-saved');

        $this->assertSame(1000, (int) $existing->refresh()->nominal_anggaran);
    }

    /**
     * @test
     */
    public function a_penetapan_without_spending_can_change_kategori_and_bidang(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.update'], '99999901');
        $existing = $this->penetapanTersimpan();
        $kategoriLain = $this->kategori();
        $bidangLain = $this->bidang();

        $this->travelTo($this->insidePeriod());

        $test = Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id);

        $this->assertDoesNotMatchRegularExpression('/<select(?=[^>]*id="anggaran-id")(?=[^>]*\sdisabled)[^>]*>/', $test->html());

        $test->set('anggaranId', (string) $kategoriLain->id)
            ->set('bidangId', (string) $bidangLain->id)
            ->call('create')
            ->assertHasNoErrors()
            ->assertDispatched('data-saved');

        $existing->refresh();

        $this->assertSame((int) $kategoriLain->id, (int) $existing->anggaran_id);
        $this->assertSame((int) $bidangLain->id, (int) $existing->bidang_id);
    }

    /**
     * @test
     *
     * The calendar guard applies to edits as well, not only to new penetapan —
     * otherwise a budget closed for the year could still be moved.
     */
    public function refuses_an_update_outside_the_configured_period(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.update'], '99999901');
        $existing = $this->penetapanTersimpan();

        $this->travelTo($this->outsidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->set('nominalAnggaran', 999999)
            ->call('create')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-saved');

        $this->assertSame(500000, (int) $existing->refresh()->nominal_anggaran);
    }

    /**
     * @test
     */
    public function refuses_an_update_without_the_update_permission(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.create'], '99999901');
        $existing = $this->penetapanTersimpan();

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->set('nominalAnggaran', 999999)
            ->call('create')
            ->assertDispatched('data-denied');

        $this->assertSame(500000, (int) $existing->refresh()->nominal_anggaran);
    }

    /**
     * @test
     */
    public function refuses_a_delete_without_the_delete_permission(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.update'], '99999901');
        $existing = $this->penetapanTersimpan();

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->call('delete')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-deleted');

        $this->assertSame(1, AnggaranBidang::query()->count());
    }

    /**
     * @test
     *
     * A Penetapan RKAT with Pemakaian Anggaran charged to it cannot be deleted.
     * The refusal says how many Pemakaian hold it, rather than the generic
     * error the foreign key used to produce, and leaves both rows alone.
     */
    public function refuses_to_delete_a_penetapan_that_already_has_spending_reported(): void
    {
        $petugas = $this->petugasWithPermissions(['keuangan.rkat-penetapan.delete'], '99999901');
        $existing = $this->penetapanTersimpan();

        foreach (['Pembelian Uji A', 'Pembelian Uji B'] as $judul) {
            PemakaianAnggaran::create([
                'judul'              => $judul,
                'tgl_dipakai'        => '2026-03-01',
                'anggaran_bidang_id' => $existing->id,
                'user_id'            => '99999901',
            ]);
        }

        $this->travelTo($this->insidePeriod());

        Livewire::actingAs($petugas)
            ->test(RKATInputPenetapan::class)
            ->dispatch('prepare', $existing->id)
            ->call('delete')
            ->assertDispatched('data-denied')
            ->assertNotDispatched('data-deleted')
            ->assertNotDispatched('data-errored')
            ->assertSee('sudah memiliki 2 Pemakaian Anggaran');

        $this->assertSame(1, AnggaranBidang::query()->count());
        $this->assertSame(2, PemakaianAnggaran::query()->count());
    }
}
