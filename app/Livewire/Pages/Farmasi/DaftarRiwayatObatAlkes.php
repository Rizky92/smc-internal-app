<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Obat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class DaftarRiwayatObatAlkes extends Component
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
            'tglAwal' => ['except' => now()->subYear()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.daftar-riwayat-obat-alkes')
            ->layout(BaseLayout::class, ['title' => 'Daftar Riwayat Obat/Alkes']);
    }

    public function getDataRiwayatObatProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->daftarRiwayat('obat', $this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_obat');
    }

    public function getDataRiwayatAlkesProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->daftarRiwayat('alkes', $this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_alkes');
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->subYear()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }

    public function searchData(): void
    {
        $this->resetPage('page_obat');
        $this->resetPage('page_alkes');

        $this->emit('$refresh');
    }

    protected function dataPerSheet(): array
    {
        $map = fn (Obat $model): array => [
            'kode_brng'                         => $model->kode_brng,
            'nama_brng'                         => $model->nama_brng,
            'stok_akhir'                        => $model->stok_akhir,
            'order_terakhir'                    => $model->order_terakhir ?? '-',
            'keterangan_order_terakhir'         => $model->keterangan_order_terakhir ?? '-',
            'tanggal_order_terakhir'            => $model->tanggal_order_terakhir ?? '-',
            'status_order_terakhir'             => $model->status_order_terakhir ?? '-',
            'posisi_order_terakhir'             => $model->posisi_order_terakhir ?? '-',
            'penggunaan_terakhir'               => $model->penggunaan_terakhir ?? '-',
            'keterangan_penggunaan_terakhir'    => $model->keterangan_penggunaan_terakhir ?? '-',
            'tanggal_penggunaan_terakhir'       => $model->tanggal_penggunaan_terakhir ?? '-',
            'status_penggunaan_terakhir'        => $model->status_penggunaan_terakhir ?? '-',
            'posisi_penggunaan_terakhir'        => $model->posisi_penggunaan_terakhir ?? '-',
        ];

        return [
            'obat' => Obat::query()
                ->daftarRiwayat('obat', $this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map($map),
            'alkes' => Obat::query()
                ->daftarRiwayat('alkes', $this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map($map),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Item',
            'Nama Item',
            'Stok Akhir',
            'Order Terakhir',
            'Keterangan Order Terakhir',
            'Tanggal Order Terakhir',
            'Status Order Terakhir',
            'Posisi Order Terakhir',
            'Penggunaan Terakhir',
            'Keterangan Penggunaan Terakhir',
            'Tanggal Penggunaan Terakhir',
            'Status Penggunaan Terakhir',
            'Posisi Penggunaan Terakhir',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s/d ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Daftar Riwayat Obat/Alkes',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
