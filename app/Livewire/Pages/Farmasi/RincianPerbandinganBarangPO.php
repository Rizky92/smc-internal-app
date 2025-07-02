<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\PenerimaanObat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class RincianPerbandinganBarangPO extends Component
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

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getRincianPerbandinganBarangPOProperty()
    {
        return $this->isDeferred ? [] : PenerimaanObat::query()
            ->rincianPerbandinganPemesananPO('obat', $this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_obat');
    }

    public function getRincianPerbandinganAlkesPOProperty()
    {
        return $this->isDeferred ? [] : PenerimaanObat::query()
            ->rincianPerbandinganPemesananPO('alkes', $this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_alkes');
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.rincian-perbandingan-barang-p-o')
            ->layout(BaseLayout::class, ['title' => 'Rincian Perbandingan Barang PO Per Bulan']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
    }

    protected function dataPerSheet(): array
    {
        $map = fn (PenerimaanObat $model): array => [
            'kode_brng'                  => $model->kode_brng,
            'nama_brng'                  => $model->nama_brng,
            'harga_satuan'               => $model->harga_satuan,
            'total_pemesanan'            => $model->total_pemesanan,
            'total_harga'                => $model->total_harga,
            'total_pemesanan_bulan_lalu' => $model->total_pemesanan_bulan_lalu,
            'total_harga_bulan_lalu'     => $model->total_harga_bulan_lalu,
            'selisih_pemesanan'          => $model->selisih_pemesanan,
            'selisih_harga'              => $model->selisih_harga,
        ];

        return [
            'obat' => fn () => PenerimaanObat::query()
                ->rincianPerbandinganPemesananPO('obat', $this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map($map),
            'alkes' => fn () => PenerimaanObat::query()
                ->rincianPerbandinganPemesananPO('alkes', $this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map($map),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Barang',
            'Nama Barang',
            'Harga Satuan',
            'Total Pemesanan',
            'Total Harga',
            'Total Pemesanan Bulan Lalu',
            'Total Harga Bulan Lalu',
            'Selisih Pemesanan',
            'Selisih Harga',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periodeAwalBulanLalu = carbon($this->tglAwal)->subMonth();
        $periodeAkhirBulanLalu = carbon($this->tglAkhir)->subMonth();

        $periode = 'Perbandingan Periode Antara'.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y').' dengan '.$periodeAwalBulanLalu->translatedFormat('d F Y').' s.d. '.$periodeAkhirBulanLalu->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Rincian Perbandingan Barang PO Per Bulan',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
