<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Obat;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class RingkasanPerbandinganBarangPO extends Component
{
    use WithPagination;

    public $cari;

    public $periodeAwal;

    public $periodeAkhir;

    public $perpage;

    public $berdasarkan;

    public $statusPemesanan;

    public $statusPenerimaan;

    protected $paginationTheme = 'bootstrap';

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
            ],
            'perpage' => [
                'except' => 25,
            ],
            'periodeAwal' => [
                'except' => now()->startOfMonth()->format('Y-m-d'),
                'as' => 'periode_awal',
            ],
            'periodeAkhir' => [
                'except' => now()->endOfMonth()->format('Y-m-d'),
                'as' => 'periode_akhir',
            ],
        ];
    }

    public function mount()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->berdasarkan = 'tanggal datang';
        $this->statusPemesanan = 'sudah datang';
        $this->statusPenerimaan = 'sudah dibayar';
    }

    public function getPerbandinganOrderObatPOProperty()
    {
        return Obat::perbandinganObatPO(
            $this->periodeAwal,
            $this->periodeAkhir,
            $this->berdasarkan,
            $this->statusPemesanan,
            $this->statusPenerimaan
        )->paginate($this->perpage);
    }

    public function getKriteriaBerdasarkanProperty()
    {
        return [
            'tanggal pesan',
            'tanggal datang'
        ];
    }

    public function getKriteriaStatusPemesananProperty()
    {
        return [
            'proses pesan',
            'sudah datang'
        ];
    }

    public function getKriteriaStatusPenerimaanProperty()
    {
        return [
            'Sudah Dibayar',
            'Belum Dibayar',
            'Belum Lunas',
            'Titip Faktur',
        ];
    }

    public function render()
    {
        return view('livewire.farmasi.ringkasan-perbandingan-barang-p-o')
            ->layout(BaseLayout::class, ['title' => 'Ringkasan Perbandingan Barang PO Farmasi']);
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
