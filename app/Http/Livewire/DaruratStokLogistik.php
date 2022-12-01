<?php

namespace App\Http\Livewire;

use App\Exports\LogistikDaruratStok;
use App\Models\Nonmedis\BarangNonmedis;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class DaruratStokLogistik extends Component
{
    public $cari;

    public $tampilkanSaranOrderNol;

    protected $listeners = [
        'refreshFilter' => '$refresh',
        'processExcelExport',
    ];

    public function mount()
    {
        $this->cari = '';
        $this->tampilkanSaranOrderNol = true;
    }

    public function processExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "excel/{$timestamp}_darurat_stok_logistik.xlsx";

        (new LogistikDaruratStok($this->cari, $this->tampilkanSaranOrderNol))
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
        $barang = BarangNonmedis::daruratStok($this->cari, $this->tampilkanSaranOrderNol)
            ->paginate();
            
        return view('livewire.darurat-stok-logistik', [
            'barangLogistik' => $barang,
        ]);
    }
}
