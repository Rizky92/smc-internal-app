<?php

namespace Tests\Feature\Livewire\Dapur;

use App\Livewire\Pages\Dapur\InputMinmaxStok;
use App\Models\Dapur\MinmaxStokBarangDapur;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: setting a kitchen item's min/max reorder thresholds.
 *
 * simpan() writes to mysql_smc (minmax_stok_dapur) while the list itself
 * reads mysql_sik's dapurbarang joined against that same table - the two
 * connections meeting is exactly the kind of seam a route-200 check would
 * miss, so one test writes through simpan() and reads back through the same
 * join the list uses.
 */
class InputMinmaxStokTest extends TestCase
{
    private const KODE_BARANG = 'UJI-BRG-001';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');
        $smc = DB::connection('mysql_smc');

        $sik->table('dapurbarang')->where('kode_brng', 'like', 'UJI-%')->delete();
        $sik->table('kodesatuan')->where('kode_sat', 'UJI')->delete();
        // Soft-deleted by the model; delete the row outright so it doesn't
        // leak into another test's read of the same kode_brng.
        $smc->table('minmax_stok_dapur')->where('kode_brng', 'like', 'UJI-%')->delete();

        parent::tearDown();
    }

    private function barang(string $kodeBrng, int $stok = 10): void
    {
        $sik = DB::connection('mysql_sik');

        // dapurbarang.kode_sat is a foreign key into kodesatuan.
        $sik->table('kodesatuan')->updateOrInsert(['kode_sat' => 'UJI'], ['satuan' => 'Satuan Uji']);

        $sik->table('dapurbarang')->insert([
            'kode_brng' => $kodeBrng,
            'nama_brng' => 'Barang Uji '.$kodeBrng,
            'kode_sat'  => 'UJI',
            'jenis'     => 'Kering',
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

        $this->assertNull(MinmaxStokBarangDapur::find(self::KODE_BARANG));
    }

    /**
     * @test
     */
    public function creates_a_new_minmax_entry(): void
    {
        $petugas = $this->petugasWithPermissions(['dapur.stok-minmax.update'], '99999901');

        Livewire::actingAs($petugas)
            ->test(InputMinmaxStok::class)
            ->call('simpan', self::KODE_BARANG, 5, 20, 'SUP01')
            ->assertDispatched('data-tersimpan')
            ->assertSee('Data berhasil disimpan!');

        $minmax = MinmaxStokBarangDapur::findOrFail(self::KODE_BARANG);
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
        $petugas = $this->petugasWithPermissions(['dapur.stok-minmax.update'], '99999901');

        Livewire::actingAs($petugas)
            ->test(InputMinmaxStok::class)
            ->call('simpan', self::KODE_BARANG, 5, 20);

        $this->assertNull(MinmaxStokBarangDapur::findOrFail(self::KODE_BARANG)->kode_suplier);
    }

    /**
     * @test
     *
     * updateOrCreate() keyed on kode_brng: saving the same item again updates
     * its thresholds in place instead of creating a duplicate row.
     */
    public function updates_an_existing_entry_instead_of_duplicating(): void
    {
        $petugas = $this->petugasWithPermissions(['dapur.stok-minmax.update'], '99999901');

        MinmaxStokBarangDapur::create([
            'kode_brng'    => self::KODE_BARANG,
            'stok_min'     => 1,
            'stok_max'     => 2,
            'kode_suplier' => null,
        ]);

        Livewire::actingAs($petugas)
            ->test(InputMinmaxStok::class)
            ->call('simpan', self::KODE_BARANG, 5, 20, 'SUP02');

        $this->assertSame(1, MinmaxStokBarangDapur::withTrashed()->where('kode_brng', self::KODE_BARANG)->count());

        $minmax = MinmaxStokBarangDapur::findOrFail(self::KODE_BARANG);
        $this->assertSame(5, $minmax->stok_min);
        $this->assertSame(20, $minmax->stok_max);
        $this->assertSame('SUP02', $minmax->kode_suplier);
    }

    /**
     * @test
     *
     * The list's stok_min/stok_max come from a left join against
     * minmax_stok_dapur - reads back whatever simpan() just wrote, across
     * both connections.
     */
    public function shows_the_saved_thresholds_in_the_list(): void
    {
        $this->barang(self::KODE_BARANG);

        $petugas = $this->petugasWithPermissions(['dapur.stok-minmax.update'], '99999901');

        $component = Livewire::actingAs($petugas)
            ->test(InputMinmaxStok::class)
            ->call('loadProperties')
            ->call('simpan', self::KODE_BARANG, 5, 20)
            ->call('loadProperties');

        $item = collect($component->get('barangDapur')->items())
            ->firstWhere('kode_brng', self::KODE_BARANG);

        $this->assertNotNull($item, 'Barang uji tidak ditemukan pada daftar setelah disimpan.');
        $this->assertSame(5, (int) $item->stok_min);
        $this->assertSame(20, (int) $item->stok_max);
    }
}
