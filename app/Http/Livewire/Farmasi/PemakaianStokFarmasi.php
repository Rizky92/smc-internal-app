<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Obat;
use App\Support\Livewire\Concerns\DeferredLoading;
use App\Support\Livewire\Concerns\ExcelExportable;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\LiveTable;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class PemakaianStokFarmasi extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public function mount(): void
    {
        $this->defaultValues();
    }
  /**
     * @return \Illuminate\Contracts\Pagination\Paginator|array<empty, empty>
     */
    public function getPemakaianStokObatProperty()
    {
        return $this->isDeferred
            ? []
            : Obat::query()
                ->pemakaianStok()
                ->search($this->cari, [
                    'databarang.kode_brng',
                    'nama_brng',
                    'kodesatuan.satuan',
                ])
                ->sortWithColumns($this->sortColumns, [
                    'ke_pasien_14_hari'   => DB::raw("(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 2 week) and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 2 week) and current_date()), 0))"),
                    'pemakaian_1_minggu'  => DB::raw("(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 1 week) and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 1 week) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 1 week) and current_date()), 0))"),
                    'pemakaian_1_bulan'   => DB::raw("(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 1 month) and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 1 month) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 1 month) and current_date()), 0))"),
                    'pemakaian_3_bulan'   => DB::raw("(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 3 month) and current_date()), 0) + ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 3 month) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 3 month) and current_date()), 0))"),
                    'pemakaian_10_bulan'   => DB::raw("(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 10 month) and current_date()), 0) +  ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 10 month) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 10 month) and current_date()), 0))"),
                    'pemakaian_12_bulan'   => DB::raw("(ifnull((select round(sum(detail_pemberian_obat.jml), 2) from detail_pemberian_obat where detail_pemberian_obat.kode_brng = databarang.kode_brng and detail_pemberian_obat.tgl_perawatan between date_sub(current_date(), interval 1 year) and current_date()), 0) +  ifnull((select round(sum(detailjual.jumlah), 2) from detailjual join penjualan on detailjual.nota_jual = penjualan.nota_jual where detailjual.kode_brng = databarang.kode_brng and penjualan.tgl_jual between date_sub(current_date(), interval 1 year) and current_date()), 0) + ifnull((select round(sum(detail_pengeluaran_obat_bhp.jumlah), 2) from detail_pengeluaran_obat_bhp join pengeluaran_obat_bhp on detail_pengeluaran_obat_bhp.no_keluar = pengeluaran_obat_bhp.no_keluar where detail_pengeluaran_obat_bhp.kode_brng = databarang.kode_brng and pengeluaran_obat_bhp.tanggal between date_sub(current_date(), interval 1 year) and current_date()), 0))"),
                ])
                ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.farmasi.pemakaian-stok-farmasi')
            ->layout(BaseLayout::class, ['title' => 'Pemakaian Stok Farmasi']);
    }

    protected function defaultValues(): void
    {
        //
    }

    protected function dataPerSheet(): array
    {
        return [
            Obat::query()
            ->pemakaianStok()
            ->get()
            ->map(fn (Obat $model, $_):array => [
                'kode_brng' => $model->kode_brng,
                'nama_brng' => $model->nama_brng,
                'satuan_kecil' =>$model->satuan_kecil,
                'ke_pasien_14_hari' =>$model->ke_pasien_14_hari,
                'pemakaian_3_bulan' =>$model->pemakaian_3_bulan,
                'pemakaian_1_bulan' =>$model->pemakaian_1_bulan,
                'pemakaian_1_minggu' =>$model->pemakaian_1_minggu,
                'pemakaian_10_bulan' =>$model->pemakaian_10_bulan,
                'pemakaian_12_bulan' =>$model->pemakaian_12_bulan,
            ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode',
            'Nama',
            'Satuan kecil',
            'Ke Pasien (14 Hari)',
            'Pemakaian 3 Bulan',
            'Pemakaian 1 Bulan',
            'Pemakaian 1 Minggu',
            'Pemakaian 10 Bulan',
            'Pemakaian 12 Bulan',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Pemakaian Stok Farmasi',
            'Per ' . now()->translatedFormat('d F Y'),
        ];
    }
}
