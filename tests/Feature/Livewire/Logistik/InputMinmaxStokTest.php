<?php

namespace Tests\Feature\Livewire\Logistik;

use App\Livewire\Pages\Logistik\InputMinmaxStok;
use App\Models\Logistik\MinmaxStokBarangNonMedis;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: setting a non-medical item's min/max reorder thresholds.
 *
 * Same shape as Dapur\InputMinmaxStok - simpan() writes to mysql_smc
 * (ipsrs_minmax_stok_barang) while the list reads mysql_sik's ipsrsbarang
 * joined against that same table, so one test writes through simpan() and
 * reads back through the join the list uses. The list's raw select aliases
 * the joined columns as stokmin/stokmax (no underscore) - different from
 * Dapur's stok_min/stok_max - so this suite reads them under that name.
 */
class InputMinmaxStokTest extends TestCase
{
    private const KODE_BARANG = 'UJI-BRG-001';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');
        $smc = DB::connection('mysql_smc');

        $sik->table('ipsrsbarang')->where('kode_brng', 'like', 'UJI-%')->delete();
        $sik->table('kodesatuan')->where('kode_sat', 'UJI')->delete();
        $sik->table('ipsrsjenisbarang')->where('kd_jenis', 'UJI01')->delete();
        // Soft-deleted by the model; delete the row outright so it doesn't
        // leak into another test's read of the same kode_brng.
        $smc->table('ipsrs_minmax_stok_barang')->where('kode_brng', 'like', 'UJI-%')->delete();

        parent::tearDown();
    }

    private function barang(string $kodeBrng, int $stok = 10): void
    {
        $sik = DB::connection('mysql_sik');

        // ipsrsbarang.kode_sat and .jenis are foreign keys into kodesatuan
        // and ipsrsjenisbarang respectively.
        $sik->table('kodesatuan')->updateOrInsert(['kode_sat' => 'UJI'], ['satuan' => 'Satuan Uji']);
        $sik->table('ipsrsjenisbarang')->updateOrInsert(['kd_jenis' => 'UJI01'], ['nm_jenis' => 'Jenis Uji']);

        $sik->table('ipsrsbarang')->insert([
            'kode_brng' => $kodeBrng,
            'nama_brng' => 'Barang Uji '.$kodeBrng,
            'kode_sat'  => 'UJI',
            'jenis'     => 'UJI01',
            'stok'      => $stok,
            'harga'     => 5000,
            'status'    => '1',
        ]);
    }

    /**
     * @test
     */
    public function refuses_to_save_without_permission(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        Livewire::actingAs($petugas)
            ->test(InputMinmaxStok::class)
            ->call('simpan', self::KODE_BARANG, 5, 20)
            ->assertSee('Anda tidak memiliki izin untuk mengupdate barang');

        $this->assertNull(MinmaxStokBarangNonMedis::find(self::KODE_BARANG));
    }

    /**
     * @test
     */
    public function creates_a_new_minmax_entry(): void
    {
        $petugas = $this->petugasWithPermissions(['logistik.stok-minmax.update'], '99999901');

        Livewire::actingAs($petugas)
            ->test(InputMinmaxStok::class)
            ->call('simpan', self::KODE_BARANG, 5, 20, 'SUP01')
            ->assertDispatched('data-tersimpan')
            ->assertSee('Data berhasil disimpan!');

        $minmax = MinmaxStokBarangNonMedis::findOrFail(self::KODE_BARANG);
        $this->assertSame(5, $minmax->stok_min);
        $this->assertSame(20, $minmax->stok_max);
        $this->assertSame('SUP01', $minmax->kode_suplier);
    }

    /**
     * @test
     *
     * The modal's supplier select uses "-" as its placeholder value - saving
     * without picking a real supplier must store null, not the literal "-".
     */
    public function stores_no_supplier_selection_as_null(): void
    {
        $petugas = $this->petugasWithPermissions(['logistik.stok-minmax.update'], '99999901');

        Livewire::actingAs($petugas)
            ->test(InputMinmaxStok::class)
            ->call('simpan', self::KODE_BARANG, 5, 20);

        $this->assertNull(MinmaxStokBarangNonMedis::findOrFail(self::KODE_BARANG)->kode_suplier);
    }

    /**
     * @test
     *
     * updateOrCreate() keyed on kode_brng: saving the same item again updates
     * its thresholds in place instead of creating a duplicate row.
     */
    public function updates_an_existing_entry_instead_of_duplicating(): void
    {
        $petugas = $this->petugasWithPermissions(['logistik.stok-minmax.update'], '99999901');

        MinmaxStokBarangNonMedis::create([
            'kode_brng'    => self::KODE_BARANG,
            'stok_min'     => 1,
            'stok_max'     => 2,
            'kode_suplier' => null,
        ]);

        Livewire::actingAs($petugas)
            ->test(InputMinmaxStok::class)
            ->call('simpan', self::KODE_BARANG, 5, 20, 'SUP02');

        $this->assertSame(1, MinmaxStokBarangNonMedis::withTrashed()->where('kode_brng', self::KODE_BARANG)->count());

        $minmax = MinmaxStokBarangNonMedis::findOrFail(self::KODE_BARANG);
        $this->assertSame(5, $minmax->stok_min);
        $this->assertSame(20, $minmax->stok_max);
        $this->assertSame('SUP02', $minmax->kode_suplier);
    }

    /**
     * @test
     *
     * The list's stokmin/stokmax come from a left join against
     * ipsrs_minmax_stok_barang - reads back whatever simpan() just wrote,
     * across both connections.
     */
    public function shows_the_saved_thresholds_in_the_list(): void
    {
        $this->barang(self::KODE_BARANG);

        $petugas = $this->petugasWithPermissions(['logistik.stok-minmax.update'], '99999901');

        $component = Livewire::actingAs($petugas)
            ->test(InputMinmaxStok::class)
            ->call('loadProperties')
            ->call('simpan', self::KODE_BARANG, 5, 20)
            ->call('loadProperties');

        $item = collect($component->get('barangLogistik')->items())
            ->firstWhere('kode_brng', self::KODE_BARANG);

        $this->assertNotNull($item, 'Barang uji tidak ditemukan pada daftar setelah disimpan.');
        $this->assertSame(5, (int) $item->stokmin);
        $this->assertSame(20, (int) $item->stokmax);
    }
}
