<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Obat;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class StokDaruratFarmasi extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\Paginator|array<empty, empty>
     */
    public function getStokDaruratObatProperty()
    {
        return $this->isDeferred
            ? []
            : Obat::query()
            ->daruratStok()
            ->search($this->cari, [
                'databarang.kode_brng',
                'nama_brng',
                'kodesatuan.satuan',
                'kategori_barang.nama',
                'industrifarmasi.nama_industri',
            ])
            ->sortWithColumns($this->sortColumns, [
                'satuan_kecil'        => 'kodesatuan.satuan',
                'kategori'            => 'kategori_barang.nama',
                'stok_sekarang_ap'    => DB::raw('ifnull(round(stok_gudang_ap.stok_di_gudang, 2), 0)'),
                'stok_sekarang_ifi'   => DB::raw('ifnull(round(stok_gudang_ifi.stok_di_gudang, 2), 0)'),
                'saran_order'         => DB::raw('(databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0))'),
                'harga_beli'          => DB::raw('round(databarang.h_beli)'),
                'harga_beli_total'    => DB::raw('round((databarang.stokminimal - ifnull(stok_gudang_ap.stok_di_gudang, 0)) * databarang.h_beli)'),
                'harga_beli_terakhir' => DB::raw("(select ifnull(round(dp.h_pesan / databarang.isi, 2), 0) from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1)"),
                'diskon_terakhir'     => DB::raw("(select ifnull(dp.dis, '0') from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1)"),
                'supplier_terakhir'   => DB::raw("(select ifnull(ds.nama_suplier, '-') from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur left join datasuplier ds on p.kode_suplier = ds.kode_suplier where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1)"),
                'ke_pasien_14_hari'   => DB::raw("(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(now(), interval 2 week) and now()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(now(), interval 2 week) and now()), 0))"),
                'pemakaian_3_bulan'   => DB::raw("(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(now(), interval 3 month) and now()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(now(), interval 3 month) and now()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(now(), interval 3 month) and now()), 0))"),
                'pemakaian_1_bulan'   => DB::raw("(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(now(), interval 1 month) and now()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(now(), interval 1 month) and now()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(now(), interval 1 month) and now()), 0))"),
                'pemakaian_1_minggu'  => DB::raw("(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(now(), interval 1 week) and now()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(now(), interval 1 week) and now()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(now(), interval 1 week) and now()), 0))"),
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.farmasi.stok-darurat-farmasi')
            ->layout(BaseLayout::class, ['title' => 'Darurat Stok Farmasi']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
    }

    protected function dataPerSheet(): array
    {
        return [
            Obat::query()
                ->daruratStok()
                ->get()
                ->map(fn (Obat $model): array => [
                    'nama_brng'           => $model->nama_brng,
                    'satuan_kecil'        => $model->satuan_kecil,
                    'kategori'            => $model->kategori,
                    'stokminimal'         => floatval($model->stokminimal),
                    'stok_sekarang_ifi'   => floatval($model->stok_sekarang_ifi),
                    'stok_sekarang_ap'    => floatval($model->stok_sekarang_ap),
                    'ke_pasien_14_hari'   => floatval($model->ke_pasien_14_hari),
                    'saran_order'         => floatval($model->saran_order),
                    'nama_industri'       => $model->nama_industri,
                    'harga_beli'          => floatval($model->harga_beli),
                    'harga_beli_total'    => floatval($model->harga_beli_total),
                    'harga_beli_terakhir' => floatval($model->harga_beli_terakhir),
                    'diskon_terakhir'     => floatval($model->diskon_terakhir),
                    'supplier_terakhir'   => $model->supplier_terakhir,
                    'pemakaian_3_bulan'   => floatval($model->pemakaian_3_bulan),
                    'pemakaian_1_bulan'   => floatval($model->pemakaian_1_bulan),
                    'pemakaian_1_minggu'  => floatval($model->pemakaian_1_minggu),
                ])
                ->all(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Nama',
            'Satuan kecil',
            'Kategori',
            'Stok Minimal',
            'Stok Farmasi RWI',
            'Stok Farmasi B',
            'Ke Pasien (14 Hari)',
            'Saran Order',
            'Supplier',
            'Harga per Unit (Rp)',
            'Total Harga (Rp)',
            'Harga Beli Terakhir (Rp)',
            'Diskon Terakhir (%)',
            'Supplier Terakhir',
            'Pemakaian 3 Bulan',
            'Pemakaian 1 Bulan',
            'Pemakaian 1 Minggu',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Darurat Stok Farmasi',
            'Per ' . now()->translatedFormat('d F Y'),
        ];
    }
}
