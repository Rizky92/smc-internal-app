<?php

namespace App\Http\Livewire\RekamMedis;

use App\Models\RekamMedis\StatistikRekamMedis;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanStatistik extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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

    public function getDataLaporanStatistikProperty()
    {
        return $this->isDeferred
            ? []
            : StatistikRekamMedis::query()
                ->search($this->cari)
                ->whereBetween('tgl_masuk', [$this->periodeAwal, $this->periodeAkhir])
                ->orderBy('no_rawat')
                ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.rekam-medis.laporan-statistik')
            ->layout(BaseLayout::class, ['title' => 'Laporan Statistik']);
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            StatistikRekamMedis::query()
                ->whereBetween('tgl_masuk', [$this->periodeAwal, $this->periodeAkhir])
                ->orderBy('no_rawat')
                ->cursor()
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No RM',
            'Pasien',
            'NIK',
            'L / P',
            'Tgl. Lahir',
            'Umur',
            'Agama',
            'Suku',
            'Jenis Perawatan',
            'Pasien Lama / Baru',
            'Asal Poli',
            'Dokter Poli',
            'Status Ralan',
            'Tgl. Masuk',
            'Jam Masuk',
            'Tgl. Pulang',
            'Jam Pulang',
            'Diagnosa Masuk',
            'ICD Diagnosa',
            'Diagnosa',
            'ICD Tindakan Ralan',
            'Tindakan Ralan',
            'ICD Tindakan Ranap',
            'Tindakan Ranap',
            'Lama Operasi',
            'Rujukan Masuk',
            'DPJP Ranap',
            'Kelas',
            'Penjamin',
            'Status Bayar',
            'Status Pulang',
            'Rujukan Keluar',
            'No. HP',
            'Alamat',
            'Kunjungan ke',
        ];
    }

    protected function pageHeaders(): array
    {
        $dateStart = Carbon::parse($this->periodeAwal)->format('d F Y');
        $dateEnd = Carbon::parse($this->periodeAkhir)->format('d F Y');

        return [
            'RS Samarinda Medika Citra',
            'Laporan Statistik Rekam Medis',
            "{$dateStart} - {$dateEnd}",
        ];
    }
}
