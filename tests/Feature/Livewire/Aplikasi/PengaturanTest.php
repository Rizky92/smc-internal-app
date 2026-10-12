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
     * The years that have a Penetapan RKAT, the saved Tahun RKAT, and this year
     * and next so the Tahun RKAT can be advanced before anything is set for it —
     * newest first, with no years in between that hold nothing.
     */
    public function offers_the_years_with_penetapan_the_tahun_rkat_and_this_year_and_next(): void
    {
        $anggaran = Anggaran::create(['nama' => 'Kategori Uji Pengaturan']);
        $bidang = Bidang::create(['nama' => 'Bidang Uji Pengaturan', 'parent_id' => null]);

        AnggaranBidang::create([
            'anggaran_id'      => $anggaran->id,
            'bidang_id'        => $bidang->id,
            'tahun'            => 2020,
            'nominal_anggaran' => 1000000,
        ]);

        app(RKATSettings::class)->fill(['tahun' => 2023])->save();

        $this->travelTo(carbon('2026-09-17'));

        $tahun = Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(Pengaturan::class)
            ->get('dataTahun');

        $this->assertSame([2027, 2026, 2023, 2020], array_keys($tahun));
    }

    /**
     * @test
     *
     * A fresh install has no Penetapan RKAT at all. The list used to start at
     * year 0 in that state, two thousand entries long.
     */
    public function offers_a_short_list_before_any_penetapan_exists(): void
    {
        $this->assertSame(0, AnggaranBidang::query()->count(), 'Test ini mengandaikan belum ada anggaran_bidang.');

        app(RKATSettings::class)->fill(['tahun' => 2026])->save();

        $this->travelTo(carbon('2026-09-17'));

        $tahun = Livewire::actingAs($this->petugasWithPermissions([], '99999901'))
            ->test(Pengaturan::class)
            ->get('dataTahun');

        $this->assertSame([2027, 2026], array_keys($tahun));
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
