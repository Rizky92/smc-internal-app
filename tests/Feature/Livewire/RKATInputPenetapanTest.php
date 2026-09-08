<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\Keuangan\Modal\RKATInputPenetapan;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
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
}
