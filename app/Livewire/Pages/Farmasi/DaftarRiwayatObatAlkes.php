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
    public $tanggal;

    protected function queryString(): array
    {
        return [
            'tanggal' => ['except' => now()->format('Y-m-d'), 'as' => 'tanggal'],
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
            ->daftarRiwayat('obat',$this->tanggal)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page-obat');
    }

    public function getDataRiwayatAlkesProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->daftarRiwayat('alkes',$this->tanggal)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page-alkes');
    }

    protected function defaultValues(): void
    {
        $this->tanggal = now()->format('Y-m-d');
    }

    public function searchData(): void
    {
        $this->resetPage('page-obat');
        $this->resetPage('page-alkes');

        $this->emit('$refresh');
    }

    protected function dataPerSheet(): array
    {
        $map = fn (Obat $model): array => [
            'kode_brng'                         => $model->kode_brng,
            'nama_brng'                         => $model->nama_brng,
            'stok_akhir'                        => $model->stok_akhir,
            'order_terakhir'                    => $model->order_terakhir ?? '-',
            'penggunaan_terakhir'               => $model->penggunaan_terakhir ?? '-',
            'tanggal_order_terakhir'            => $model->tanggal_order_terakhir ?? '-',
            'tanggal_penggunaan_terakhir'       => $model->tanggal_penggunaan_terakhir ?? '-',
            'status_order_terakhir'             => $model->status_order_terakhir ?? '-',
            'status_penggunaan_terakhir'        => $model->status_penggunaan_terakhir ?? '-',
            'posisi_order_terakhir'             => $model->posisi_order_terakhir ?? '-',
            'posisi_penggunaan_terakhir'        => $model->posisi_penggunaan_terakhir ?? '-',
            'keterangan_order_terakhir'         => $model->keterangan_order_terakhir ?? '-',
            'keterangan_penggunaan_terakhir'    => $model->keterangan_penggunaan_terakhir ?? '-',
        ];

        return [
            'obat' => Obat::query()
                ->daftarRiwayat('obat', $this->tanggal)
                ->cursor()
                ->map($map),
            'alkes' => Obat::query()
                ->daftarRiwayat('alkes', $this->tanggal)
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
            'Penggunaan Terakhir',
            'Tanggal Order Terakhir',
            'Tanggal Penggunaan Terakhir',
            'Status Order Terakhir',
            'Status Penggunaan Terakhir',
            'Posisi Order Terakhir',
            'Posisi Penggunaan Terakhir',
            'Keterangan Order Terakhir',
            'Keterangan Penggunaan Terakhir',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tanggal)->subYear();
        $periodeAkhir = carbon($this->tanggal);
        
        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

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
