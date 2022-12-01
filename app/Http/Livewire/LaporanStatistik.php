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

    public function refreshPage()
    {
        $file = "excel/{$this->timestamp}_rekammedis.xlsx";

        $fileExists = Storage::disk('public')->has($file);

        if (!$fileExists) {
            $this->emit('refreshFilter');
        } else {
            session()->flash('excel.download', Storage::disk('public')->url($file));
            session()->flash('excel.exported', 'File berhasil diproses! Mulai download atau klik');
    
            Storage::disk('public')->download($file);
        }
    }

    public function exportToExcel()
    {
        $this->timestamp = now()->format('Ymd_His');

        (new RekamMedisExport($this->periodeAwal, $this->periodeAkhir, $this->timestamp))
            ->store("excel/{$this->timestamp}_rekammedis.xlsx", 'public');

        return Storage::disk('public')->download("excel/{$this->timestamp}_rekammedis.xlsx");
    }

    public function download()
    {
        
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
