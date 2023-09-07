<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Inventaris\SuratPemesananObat;
use App\Support\Livewire\Concerns\ExcelExportable;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\LiveTable;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class PerbandinganBarangPO extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var bool */
    public $barangSelisih;

    protected function queryString(): array
    {
        return [
            'tglAwal'       => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir'      => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'barangSelisih' => ['except' => false, 'as' => 'barang_selisih'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getPerbandinganOrderObatPOProperty(): Paginator
    {
        return SuratPemesananObat::query()
            ->perbandinganPemesananObatPO($this->tglAwal, $this->tglAkhir, $this->barangSelisih)
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
        $this->barangSelisih = false;
    }

    protected function dataPerSheet(): array
    {
        return [
            SuratPemesananObat::query()
                ->perbandinganPemesananObatPO($this->tglAwal, $this->tglAkhir, $this->barangSelisih)
                ->get()
                ->map(fn (SuratPemesananObat $model): array => [
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
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Ringkasan Perbandingan PO Obat',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
