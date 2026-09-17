<?php

namespace Tests\Feature\Livewire\Aplikasi;

use App\Livewire\Pages\Aplikasi\Pengaturan;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Settings\NPWPSettings;
use App\Settings\RKATSettings;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the settings page itself is just a shell - Concerns\SetNPWPPenjual
 * and Concerns\PengaturanRKAT carry the actual permission checks and writes,
 * and only get exercised through this host component.
 *
 * Both settings are global (spatie/laravel-settings), not scoped fixture rows,
 * so this suite snapshots the real values in setUp() and restores them in
 * tearDown() rather than leaving whatever a test happened to save behind for
 * every other test in the suite (RKATInputPenetapanTest's insidePeriod()/
 * outsidePeriod() in particular read RKATSettings directly).
 */
class PengaturanTest extends TestCase
{
    private array $originalRkat;

    private string $originalNpwp;

    protected function setUp(): void
    {
        parent::setUp();

        $rkat = app(RKATSettings::class);

        $this->originalRkat = [
            'tahun'               => $rkat->tahun,
            'tgl_penetapan_awal'  => $rkat->tgl_penetapan_awal->toDateString(),
            'tgl_penetapan_akhir' => $rkat->tgl_penetapan_akhir->toDateString(),
        ];

        $this->originalNpwp = app(NPWPSettings::class)->npwp_penjual;
    }

    protected function tearDown(): void
    {
        app(RKATSettings::class)->fill([
            'tahun'               => $this->originalRkat['tahun'],
            'tgl_penetapan_awal'  => carbon($this->originalRkat['tgl_penetapan_awal']),
            'tgl_penetapan_akhir' => carbon($this->originalRkat['tgl_penetapan_akhir']),
        ])->save();

        app(NPWPSettings::class)->fill(['npwp_penjual' => $this->originalNpwp])->save();

        $smc = DB::connection('mysql_smc');
        $smc->statement('set foreign_key_checks = 0');
        $smc->table('anggaran_bidang')->where('tahun', 2020)->delete();
        $smc->table('anggaran')->where('nama', 'Kategori Uji Pengaturan')->delete();
        $smc->table('bidang')->where('nama', 'Bidang Uji Pengaturan')->delete();
        $smc->statement('set foreign_key_checks = 1');

        parent::tearDown();
    }

    /**
     * @test
     */
    public function refuses_to_update_npwp_penjual_without_permission(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(Pengaturan::class)
            ->set('npwpPenjual', '01.234.567.8-901.000')
            ->call('updateNPWPPenjual')
            ->assertDispatched('set-npwp-penjual.data-denied');

        $this->assertSame($this->originalNpwp, app(NPWPSettings::class)->npwp_penjual);
    }

    /**
     * @test
     */
    public function requires_npwp_penjual_when_updating(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.set-npwp-penjual.update'], '99999901');

        Livewire::actingAs($petugas)
            ->test(Pengaturan::class)
            ->set('npwpPenjual', '')
            ->call('updateNPWPPenjual')
            ->assertHasErrors('npwpPenjual');
    }

    /**
     * @test
     */
    public function updates_npwp_penjual(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.set-npwp-penjual.update'], '99999901');

        Livewire::actingAs($petugas)
            ->test(Pengaturan::class)
            ->set('npwpPenjual', '01.234.567.8-901.000')
            ->call('updateNPWPPenjual')
            ->assertDispatched('set-npwp-penjual.data-saved');

        $this->assertSame('01.234.567.8-901.000', app(NPWPSettings::class)->npwp_penjual);
    }

    /**
     * @test
     */
    public function refuses_to_update_pengaturan_rkat_without_permission(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(Pengaturan::class)
            ->set('tahunRKAT', 2099)
            ->call('updatePengaturanRKAT')
            ->assertDispatched('pengaturan-rkat.data-denied');

        $this->assertSame($this->originalRkat['tahun'], app(RKATSettings::class)->tahun);
    }

    /**
     * @test
     */
    public function requires_all_fields_when_updating_pengaturan_rkat(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.pengaturan-rkat.update'], '99999901');

        Livewire::actingAs($petugas)
            ->test(Pengaturan::class)
            ->set('tahunRKAT', '')
            ->set('tglAwalPenetapanRKAT', '')
            ->set('tglAkhirPenetapanRKAT', '')
            ->call('updatePengaturanRKAT')
            ->assertHasErrors(['tahunRKAT', 'tglAwalPenetapanRKAT', 'tglAkhirPenetapanRKAT']);
    }

    /**
     * @test
     */
    public function updates_pengaturan_rkat(): void
    {
        $petugas = $this->petugasWithPermissions(['aplikasi.pengaturan-rkat.update'], '99999901');

        Livewire::actingAs($petugas)
            ->test(Pengaturan::class)
            ->set('tahunRKAT', 2099)
            ->set('tglAwalPenetapanRKAT', '2099-01-01')
            ->set('tglAkhirPenetapanRKAT', '2099-03-31')
            ->call('updatePengaturanRKAT')
            ->assertDispatched('pengaturan-rkat.data-saved');

        $rkat = app(RKATSettings::class);
        $this->assertSame(2099, $rkat->tahun);
        $this->assertSame('2099-01-01', $rkat->tgl_penetapan_awal->toDateString());
        $this->assertSame('2099-03-31', $rkat->tgl_penetapan_akhir->toDateString());
    }

    /**
     * @test
     *
     * getDataTahunProperty() starts the range at the earliest year any RKAT
     * budget was ever set for, not at the current RKATSettings year.
     */
    public function lists_years_starting_from_the_first_rkat_budget(): void
    {
        $anggaran = Anggaran::create(['nama' => 'Kategori Uji Pengaturan']);
        $bidang = Bidang::create(['nama' => 'Bidang Uji Pengaturan', 'parent_id' => null]);

        AnggaranBidang::create([
            'anggaran_id'      => $anggaran->id,
            'bidang_id'        => $bidang->id,
            'tahun'            => 2020,
            'nominal_anggaran' => 1000000,
        ]);

        $petugas = $this->petugasWithPermissions([], '99999901');

        $tahun = Livewire::actingAs($petugas)
            ->test(Pengaturan::class)
            ->get('dataTahun');

        $this->assertArrayHasKey(2020, $tahun);
        $this->assertArrayNotHasKey(2019, $tahun);
    }

    /**
     * DEFECT, recorded rather than asserted as correct.
     *
     * Before any budget has been set, the earliest year is null, and range()
     * reads it as 0 — so the year picker opens at year 0 and runs to five years
     * from now, two thousand entries long. That is the state of a fresh install,
     * and of the dev smc schema today (no anggaran_bidang rows).
     *
     * Falling back to the RKATSettings year, or the current one, fixes it. Flip
     * the assertions when it lands.
     *
     * @test
     */
    public function without_any_budget_the_year_list_currently_starts_at_year_zero(): void
    {
        $this->assertSame(0, AnggaranBidang::query()->count(), 'Test ini mengandaikan belum ada anggaran_bidang.');

        $tahun = Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(Pengaturan::class)
            ->get('dataTahun');

        $this->assertSame(0, array_key_first($tahun));
        $this->assertGreaterThan(2000, count($tahun));
    }

    /**
     * @test
     *
     * permissions() walks every trait prefixed "Pengaturan*" on the class and
     * collects each one's getXPermissions() - losing that convention on a
     * newly added Concerns trait would silently drop its permissions from
     * whatever menu/role-management screen calls Pengaturan::permissions().
     */
    public function combines_permissions_from_every_pengaturan_trait(): void
    {
        $permissions = Pengaturan::permissions();

        $this->assertStringContainsString('aplikasi.set-npwp-penjual.update', $permissions);
        $this->assertStringContainsString('aplikasi.pengaturan-rkat.read', $permissions);
        $this->assertStringContainsString('aplikasi.pengaturan-rkat.update', $permissions);
    }
}
