<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Inventaris\SuratPemesananObat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class PerbandinganBarangPO extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var bool */
    public $hanyaTampilkanBarangSelisih;

    protected function queryString(): array
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'hanyaTampilkanBarangSelisih' => ['except' => false, 'as' => 'barang_selisih'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getPerbandinganOrderObatPOProperty(): LengthAwarePaginator
    {
        return SuratPemesananObat::query()
            ->perbandinganPemesananObatPO($this->tglAwal, $this->tglAkhir, $this->hanyaTampilkanBarangSelisih)
            ->search($this->cari, [
                "surat_pemesanan_medis.no_pemesanan",
                "databarang.nama_brng",
                "datasuplier.nama_suplier",
                "ifnull(pemesanan_datang.nama_suplier, '-')",
            ])
            ->sortWithColumns($this->sortColumns, [
                'suplier_pesan' => "datasuplier.nama_suplier",
                'suplier_datang' => DB::raw("ifnull(pemesanan_datang.nama_suplier, '-')"),
                'jumlah_pesan' => "detail_surat_pemesanan_medis.jumlah2",
                'jumlah_datang' => DB::raw("ifnull(pemesanan_datang.jumlah, 0)"),
                'selisih' => DB::raw("ifnull((detail_surat_pemesanan_medis.jumlah2 - pemesanan_datang.jumlah), 'Barang belum datang')"),
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.farmasi.perbandingan-barang-p-o')
            ->layout(BaseLayout::class, ['title' => 'Ringkasan Perbandingan Barang PO Farmasi']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->hanyaTampilkanBarangSelisih = false;
    }

    protected function dataPerSheet(): array
    {
        return [
            SuratPemesananObat::query()
                ->perbandinganPemesananObatPO($this->tglAwal, $this->tglAkhir, $this->hanyaTampilkanBarangSelisih)
                ->get()
                ->map(fn (SuratPemesananObat $model) => [
                    'no_pemesanan'   => $model->no_pemesanan,
                    'nama_brng'      => $model->nama_brng,
                    'suplier_pesan'  => $model->suplier_pesan,
                    'suplier_datang' => $model->suplier_datang,
                    'jumlah_pesan'   => floatval($model->jumlah_pesan),
                    'jumlah_datang'  => floatval($model->jumlah_datang),
                    'selisih'        => floatval($model->selisih),
                ])
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Pemesanan',
            'Nama',
            'Supplier Tujuan',
            'Supplier yang Mendatangkan',
            'Jumlah Dipesan',
            'Jumlah yang Datang',
            'Selisih',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Ringkasan Perbandingan PO Obat',
            now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
