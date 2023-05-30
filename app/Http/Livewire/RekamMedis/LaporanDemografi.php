<?php

namespace App\Http\Livewire\RekamMedis;

use App\Models\RekamMedis\DemografiPasien;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LaporanDemografi extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /** 
     * @return \Illuminate\Contracts\Pagination\Paginator|array<empty, empty>
     */
    public function getDemografiPasienProperty()
    {
        return $this->isDeferred
            ? []
            : DemografiPasien::query()
                ->search($this->cari)
                ->whereBetween('tgl_registrasi', [$this->tglAwal, $this->tglAkhir])
                ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.rekam-medis.laporan-demografi')
            ->layout(BaseLayout::class, ['title' => 'Laporan Demografi Pasien']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            DemografiPasien::laporanDemografiExcel($this->tglAwal, $this->tglAkhir)->cursor(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kecamatan',
            'No. RM',
            'No. Registrasi',
            'Pasien',
            'Alamat',
            '0 - < 28 Hr',
            '28 Hr - 1 Th',
            '1 - 4 Th',
            '5 - 14 Th',
            '15 - 24 Th',
            '25 - 44 Th',
            '45 - 64 Th',
            '> 64 Th',
            'PR',
            'LK',
            'Diagnosa',
            'Agama',
            'Pendidikan',
            'Bahasa',
            'Suku',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Demografi Pasien',
            now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
