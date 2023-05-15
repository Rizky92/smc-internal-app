<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Obat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class StokDaruratFarmasi extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getStokDaruratObatProperty(): LengthAwarePaginator
    {
        return Obat::query()
            ->daruratStok()
            ->search($this->cari, [
                'databarang.kode_brng',
                'nama_brng',
                'kodesatuan.satuan',
                'kategori_barang.nama',
                'industrifarmasi.nama_industri',
            ])
            // sirkulasi menu 1
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
                'supplier_terakhir'   => DB::raw("(select ifnull(ds.nama_suplier, '-') from detailpesan dp left join pemesanan p on p.no_faktur = dp.no_faktur left join datasuplier ds on p.kode_suplier = ds.kode_suplier where dp.kode_brng = databarang.kode_brng order by p.tgl_pesan desc limit 1)")
            ], ['nama_brng' => 'asc'])
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
                ->orderBy('nama_brng')
                ->get()
                ->map(fn (Obat $model) => [
                    'nama_brng'             => $model->nama_brng,
                    'satuan_kecil'          => $model->satuan_kecil,
                    'kategori'              => $model->kategori,
                    'stokminimal'           => floatval($model->stokminimal),
                    'stok_sekarang_ifi'     => floatval($model->stok_sekarang_ifi),
                    'stok_sekarang_ap'      => floatval($model->stok_sekarang_ap),
                    'saran_order'           => floatval($model->saran_order),
                    'nama_industri'         => $model->nama_industri,
                    'harga_beli'            => floatval($model->harga_beli),
                    'harga_beli_total'      => floatval($model->harga_beli_total),
                    'harga_beli_terakhir'   => floatval($model->harga_beli_terakhir),
                    'diskon_terakhir'       => floatval($model->diskon_terakhir),
                    'supplier_terakhir'     => $model->supplier_terakhir,
                    'pasien_jumlah_14_hari' => floatval($model->pasien_jumlah_14_hari),
                    'pasien_total_14_hari'  => floatval($model->pasien_total_14_hari),
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
            'Saran Order',
            'Supplier',
            'Harga per Unit (Rp)',
            'Total Harga (Rp)',
            'Harga Beli Terakhir (Rp)',
            'Diskon Terakhir (%)',
            'Supplier Terakhir',
            'Jumlah Ke Pasien (14 Hari)',
            'Total Ke Pasien (14 Hari)',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Darurat Stok Farmasi',
            now()->translatedFormat('d F Y'),
        ];
    }
}
