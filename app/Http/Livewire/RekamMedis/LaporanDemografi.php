<?php

namespace App\Http\Livewire\RekamMedis;

use App\Models\RekamMedis\DemografiPasien;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanDemografi extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'periode_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'periode_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDemografiPasienProperty()
    {
        return !$this->isReadyToLoad
            ? []
            : DemografiPasien::query()
                ->search($this->cari)
                ->whereBetween('tgl_registrasi', [$this->periodeAwal, $this->periodeAkhir])
                ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.rekam-medis.laporan-demografi')
            ->layout(BaseLayout::class, ['title' => 'Laporan Demografi Pasien']);
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            DemografiPasien::laporanDemografiExcel($this->periodeAwal, $this->periodeAkhir)->get(),
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
            now()->format('d F Y'),
        ];
    }
}
