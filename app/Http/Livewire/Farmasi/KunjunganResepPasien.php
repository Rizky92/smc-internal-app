<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepObat;
use Livewire\Component;
use Livewire\WithPagination;

class KunjunganResepPasien extends Component
{
    use WithPagination;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->perpage = 25;
    }

    public function getKunjunganResepPasienRalanProperty()
    {
        return ResepObat::kunjunganResepPasien('ralan')->paginate($this->perpage);
    }

    public function getKunjunganResepPasienRanapProperty()
    {
        return ResepObat::kunjunganResepPasien('ranap')->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.kunjungan-resep-pasien')
            ->extends('layouts.admin', ['title' => 'Kunjungan Resep Pasien'])
            ->section('content');
    }
}
