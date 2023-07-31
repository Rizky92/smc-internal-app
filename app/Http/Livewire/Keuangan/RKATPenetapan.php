<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Settings\RKATSettings;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class RKATPenetapan extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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
            ->with(['anggaran', 'bidang'])
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
        return Bidang::pluck('nama', 'id');
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat-penetapan')
            ->layout(BaseLayout::class, ['title' => 'Penetapan RKAT']);
    }

    public function bisaTetapkanRKAT(): bool
    {
        $settings = app(RKATSettings::class);

        $user = Auth::user();

        $hasPermission = $user->can('keuangan.rkat-penetapan.create');

        $isDevelop = $user->hasRole(config('permission.superadmin_name'));

        $penetapanAwal = $settings->batas_penetapan_awal;
        $penetapanAkhir = $settings->batas_penetapan_akhir;

        return (carbon()->between($penetapanAwal, $penetapanAkhir) && $hasPermission) || $isDevelop;
    }

    protected function defaultValues(): void
    {
        $this->tahun = now()->format('Y');
    }

    protected function dataPerSheet(): array
    {
        return [
            AnggaranBidang::query()
                ->with(['anggaran', 'bidang'])
                ->whereTahun($this->tahun)
                ->get()
                ->map(fn (AnggaranBidang $model): array => [
                    'tahun'    => $model->tahun,
                    'bidang'   => $model->bidang->nama,
                    'anggaran' => $model->anggaran->nama,
                    'nominal'  => $model->nominal_anggaran,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tahun',
            'Bidang',
            'Anggaran',
            'Nominal (Rp)',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Penetapan RKAT Tahun ' . $this->tahun,
            'Per ' . now()->translatedFormat('d F Y'),
        ];
    }
}
