<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Inventaris\SuratPemesananObat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class PerbandinganBarangPO extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var bool */
    public $barangSelisih;

    protected function queryString(): array
    {
        return [
            'tglAwal'       => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'      => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'barangSelisih' => ['except' => false, 'as' => 'barang_selisih'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getPerbandinganOrderObatPOProperty()
    {
        return $this->isDeferred ? [] : SuratPemesananObat::query()
            ->perbandinganPemesananObatPO($this->tglAwal, $this->tglAkhir, $this->barangSelisih)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.perbandingan-barang-p-o')
            ->layout(BaseLayout::class, ['title' => 'Ringkasan Perbandingan Barang PO Farmasi']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
        $this->barangSelisih = false;
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => SuratPemesananObat::query()
                ->perbandinganPemesananObatPO($this->tglAwal, $this->tglAkhir, $this->barangSelisih)
                ->cursor()
                ->map(fn (SuratPemesananObat $model): array => [
                    'no_pemesanan'   => $model->no_pemesanan,
                    'nama_brng'      => $model->nama_brng,
                    'suplier_pesan'  => $model->suplier_pesan,
                    'suplier_datang' => $model->suplier_datang,
                    'jumlah_pesan'   => $model->jumlah_pesan,
                    'jumlah_datang'  => $model->jumlah_datang,
                    'selisih'        => $model->keterangan ?? $model->selisih,
                ]),
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

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

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
