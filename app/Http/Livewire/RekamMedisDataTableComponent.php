<?php

namespace App\Http\Livewire;

use App\Exports\RekamMedisExport;
use App\Registrasi;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class RekamMedisDataTableComponent extends Component
{
    use WithPagination;

    public $periodeAwal;

    public $periodeAkhir;

    public $cari;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

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
        $timestamp = now()->format('Y_m_d_H_i_s');
        $export = Excel::store(
            new RekamMedisExport($this->periodeAwal, $this->periodeAkhir), 
            "excel/{$timestamp}_rekam_medis.xlsx",
            'public'
        );

        if (!$export) {
            return response('', 204);
        }

        return Storage::disk('public')->download("excel/{$timestamp}_rekam_medis.xlsx");
    }

    public function render()
    {
        if (is_null($this->periodeAwal)) {
            $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        }

        if (is_null($this->periodeAkhir)) {
            $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        }

        return view('livewire.rekam-medis-data-table-component', [
            'statistik' => Registrasi::laporanStatistik($this->periodeAwal, $this->periodeAkhir)
                ->orderBy('no_rawat')
                ->orderBy('no_reg')
                ->paginate($this->perpage)
        ]);
    }
}
