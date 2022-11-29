<?php

namespace App\Http\Livewire;

use App\Jobs\ExportExcelRekamMedisJob;
use App\Models\Perawatan\Registrasi;
use Livewire\Component;
use Livewire\WithPagination;

class RekamMedis extends Component
{
    use WithPagination;

    public $periodeAwal;

    public $periodeAkhir;

    public $cari;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'refreshFilter' => '$refresh',
    ];

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => ''
            ],
            'periodeAwal' => [
                'except' => now()->startOfMonth()->format('d-m-Y'),
                'as' => 'periode_awal',
            ],
            'periodeAkhir' => [
                'except' => now()->endOfMonth()->format('d-m-Y'),
                'as' => 'periode_akhir',
            ],
            'perpage' => [
                'except' => 25,
            ],
        ];
    }

    public function mount()
    {
        $this->cari = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->page = 1;
        $this->perpage = 25;
    }

    public function exportToExcel()
    {
        ExportExcelRekamMedisJob::dispatch($this->periodeAwal, $this->periodeAkhir);

        session()->flash('excel.exporting', 'Sedang mengekspor...');
    }

    public function render()
    {
        return view('livewire.rekam-medis', [
            'statistik' => Registrasi::laporanStatistik($this->periodeAwal, $this->periodeAkhir)
                ->orderBy('no_rawat')
                ->orderBy('no_reg')
                ->paginate($this->perpage)
        ]);
    }
}
