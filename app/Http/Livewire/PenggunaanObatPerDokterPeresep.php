<?php

namespace App\Http\Livewire;

use App\Models\Farmasi\Resep;
use Livewire\Component;
use Livewire\WithPagination;

class PenggunaanObatPerDokterPeresep extends Component
{
    use WithPagination;

    public $periodeAwal;

    public $periodeAkhir;

    public $cari;

    public $perpage;

    public $timestamp;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'refreshFilter' => '$refresh',
        'refreshPage',
    ];
    
    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
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
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->perpage = 25;
    }

    public function render()
    {
        $obatPerdokter = Resep::penggunaanObatPerDokter(
            $this->periodeAwal,
            $this->periodeAkhir
        )->paginate($this->perpage);
        
        return view('livewire.penggunaan-obat-per-dokter-peresep', [
            'obatPerDokter' => $obatPerdokter,
        ]);
    }
}
