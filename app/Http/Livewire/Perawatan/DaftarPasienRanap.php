<?php

namespace App\Http\Livewire\Perawatan;

use App\Models\Perawatan\RegistrasiPasien;
use Livewire\Component;
use Livewire\WithPagination;

class DaftarPasienRanap extends Component
{
    use WithPagination;

    public $cari;

    public $perpage;

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
            'page' => [
                'except' => 1,
            ],
        ];
    }

    public function mount()
    {
        $this->cari = '';
        $this->perpage = 25;
    }

    public function getDaftarPasienRanapProperty()
    {
        return RegistrasiPasien::daftarPasienRanap()->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.perawatan.daftar-pasien-ranap')
            ->extends('layouts.admin')
            ->section('content');
    }
}
