<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Inventaris\GudangObat;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class StokObatRuangan extends Component
{
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

    public function getCollectionProperty(): Paginator
    {
        return GudangObat::query()
            ->stokPerRuangan($this->kodeBangsal)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns, ['databarang.nama_brng' => 'asc'])
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
        return view('livewire.pages.keuangan.stok-obat-ruangan')
            ->layout(BaseLayout::class, ['title' => 'Stok Obat Per Ruangan']);
    }

    protected function defaultValues(): void
    {
        $this->kodeBangsal = '-';
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => GudangObat::query()
                ->stokPerRuangan($this->kodeBangsal)
                ->sortWithColumns($this->sortColumns, ['nama_brng' => 'asc'])
                ->cursor()
                ->map(fn (GudangObat $model): array => [
                    'nm_bangsal'     => $model->nm_bangsal,
                    'kode_brng'      => $model->kode_brng,
                    'nama_brng'      => $model->nama_brng,
                    'kategori'       => $model->kategori,
                    'satuan'         => $model->satuan,
                    'stok'           => round(floatval($model->stok), 2),
                    'h_beli'         => round(floatval($model->h_beli), 2),
                    'projeksi_harga' => round(floatval($model->projeksi_harga), 2),
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Bangsal',
            'Kode',
            'Nama',
            'Kategori',
            'Satuan',
            'Stok Sekarang',
            'Harga (RP)',
            'Total Harga (RP)',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Stok Obat per Ruangan',
            'Per '.now()->translatedFormat('d F Y'),
        ];
    }
}
