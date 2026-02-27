<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Inventaris\GudangObat;
use App\Models\Farmasi\PenerimaanObatDetail;
use App\View\Components\BaseLayout;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class HppPembelianTerakhir extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $kodeBangsal;

    protected function queryString(): array
    {
        return [
            'kodeBangsal' => ['except' => '-', 'as' => 'ruangan'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getCollectionProperty()
    {
        return $this->isDeferred ? [] : PenerimaanObatDetail::query()
            ->hppPembelianTerakhir($this->kodeBangsal)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function getBangsalProperty(): Collection
    {
        return GudangObat::query()
            ->bangsalYangAda()
            ->pluck('nm_bangsal', 'kd_bangsal');
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.hpp-pembelian-terakhir')
            ->layout(BaseLayout::class, ['title' => 'HPP Pembelian Terakhir']);
    }

    protected function defaultValues(): void
    {
        $this->kodeBangsal = '-';
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => PenerimaanObatDetail::query()
                ->hppPembelianTerakhir($this->kodeBangsal)
                ->search($this->cari)
                ->sortWithColumns($this->sortColumns)
                ->cursor()
                ->map(fn (PenerimaanObatDetail $model): array => [
                    'nm_bangsal'        => $model->nm_bangsal,
                    'kode_brng'         => $model->kode_brng,
                    'nama_brng'         => $model->nama_brng,
                    'kode_satbesar'     => $model->kode_satbesar,
                    'isi'               => $model->isi,
                    'kode_sat'          => $model->kode_sat,
                    'kapasitas'         => $model->kapasitas,
                    'stok'              => round(floatval($model->stok), 2),
                    'h_pesan'           => $model->h_pesan,
                    'dis'               => $model->dis,
                    'harga_satuan'      => $model->harga_satuan,
                    'hpp'               => $model->hpp,
                    'total_nilai_stok'  => $model->total_nilai_stok,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Ruangan',
            'Kode',
            'Nama',
            'Satuan Besar',
            'Isi',
            'Satuan Kecil',
            'Kapasitas',
            'Stok',
            'Harga Pesan',
            'Diskon (%)',
            'Harga Satuan',
            'HPP',
            'Total Nilai Stok',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'HPP Pembelian Terakhir',
            'Per '.now()->translatedFormat('d F Y'),
        ];
    }
}
