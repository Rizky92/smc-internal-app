<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepObat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class KunjunganPerPoli extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable;

    public $cari;

    public $periodeAwal;

    public $periodeAkhir;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected function queryString()
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'periode_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'periode_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDataKunjunganResepPasienProperty()
    {
        return ResepObat::kunjunganFarmasi($this->periodeAwal, $this->periodeAkhir, $this->cari)->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.kunjungan-per-poli')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien Per Poli']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->perpage = 25;
    }

    protected function dataPerSheet(): array
    {
        return [
            ResepObat::kunjunganFarmasi($this->periodeAwal, $this->periodeAkhir, '')->get()
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. Resep',
            'Pasien',
            'Umur',
            'Tgl. Validasi',
            'Jam',
            'Dokter Peresep',
            'Dokter Poli',
            'Jenis Perawatan',
            'Asal Poli',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Kunjungan Pasien Per Poli di Farmasi',
            now()->format('d F Y'),
        ];
    }
}
