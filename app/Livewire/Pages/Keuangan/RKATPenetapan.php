<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Settings\RKATSettings;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class RKATPenetapan extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tahun;

    protected function queryString(): array
    {
        return [
            'tahun' => ['except' => now()->format('Y')],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataAnggaranBidangProperty(): Paginator
    {
        return AnggaranBidang::query()
            ->with(['anggaran', 'bidang', 'bidang.parent'])
            ->where('tahun', $this->tahun)
            ->paginate($this->perpage);
    }

    public function getDataTahunProperty(): array
    {
        $tahunAwal = AnggaranBidang::query()
            ->withCasts(['tahun' => 'int'])
            ->orderBy('tahun')
            ->limit(1)
            ->value('tahun') ?? 2023;

        $tahunAkhir = app(RKATSettings::class)->tahun;

        return collect(range($tahunAwal, $tahunAkhir, 1))
            ->mapWithKeys(fn (int $v, int $_): array => [$v => $v])
            ->all();
    }

    public function getDataBidangProperty(): Collection
    {
        return Bidang::query()
            ->with('parent')
            ->hasParent()
            ->get()
            ->mapWithKeys(fn (Bidang $model) => [$model->id => sprintf('%s - %s', $model->parent->nama, $model->nama)]);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.rkat-penetapan')
            ->layout(BaseLayout::class, ['title' => 'Penetapan RKAT']);
    }

    public function bisaTetapkanRKAT(): bool
    {
        $settings = app(RKATSettings::class);

        $hasPermission = user()->can('keuangan.rkat-penetapan.create');

        $isDevelop = user()->hasRole(config('permission.superadmin_name'));

        $penetapanAwal = $settings->tgl_penetapan_awal;
        $penetapanAkhir = $settings->tgl_penetapan_akhir;

        return (carbon()->between($penetapanAwal, $penetapanAkhir) && $hasPermission) || $isDevelop;
    }

    protected function defaultValues(): void
    {
        $this->tahun = (string) app(RKATSettings::class)->tahun;
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            AnggaranBidang::query()
                ->with(['anggaran', 'bidang'])
                ->whereTahun($this->tahun)
                ->get()
                ->map(fn (AnggaranBidang $model): array => [
                    'tahun'          => $model->tahun,
                    'bidang'         => $model->bidang->nama,
                    'anggaran'       => $model->anggaran->nama,
                    'nominal'        => $model->nominal_anggaran,
                    'tgl_ditetapkan' => $model->created_at->toDateString(),
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tahun',
            'Bidang',
            'Unit',
            'Anggaran',
            'Nominal (Rp)',
            'Tgl. Ditetapkan',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Penetapan RKAT Tahun '.$this->tahun,
            'Per '.now()->translatedFormat('d F Y'),
        ];
    }
}
