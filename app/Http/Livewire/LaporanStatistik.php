<?php

namespace App\Http\Livewire;

use App\Exports\RekamMedisExport;
use App\Jobs\ExportExcelRekamMedisJob;
use App\Models\RekamMedis;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanStatistik extends Component
{
    use WithPagination;

    public $periodeAwal;

    public $periodeAkhir;

    public $cari;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    public $timestamp = null;

    protected $listeners = [
        'refreshFilter' => '$refresh',
        'refreshPage',
        'processExcelExport',
    ];

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => ''
            ],
            'periodeAwal' => [
                'except' => now()->startOfMonth()->format('Y-m-d'),
                'as' => 'periode_awal',
            ],
            'periodeAkhir' => [
                'except' => now()->endOfMonth()->format('Y-m-d'),
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

    public function processExcelExport()
    {
        $this->timestamp = now()->format('Ymd_His');

        $filename = "excel/{$this->timestamp}_rekammedis.xlsx";

        (new RekamMedisExport($this->periodeAwal, $this->periodeAkhir))
            ->store($filename, 'public');

        return Storage::disk('public')->download($filename);
    }

    public function exportToExcel()
    {
        session()->flash('excel.exporting', 'Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('processExcelExport');
    }

    public function render()
    {
        return view('livewire.rekam-medis', [
            'statistik' => RekamMedis::whereBetween('tgl_registrasi', [$this->periodeAwal, $this->periodeAkhir])
                ->orderBy('no_rawat')
                ->paginate($this->perpage)
        ]);
    }
}
