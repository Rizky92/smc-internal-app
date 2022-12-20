<?php

namespace App\Http\Livewire\Perawatan;

use App\Models\Perawatan\Kamar;
use App\Models\Perawatan\RawatInap;
use App\Models\Perawatan\RegistrasiPasien;
use App\Support\Traits\Livewire\FlashComponent;
use Livewire\Component;
use Livewire\WithPagination;

class DaftarPasienRanap extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
    ];

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
        return RegistrasiPasien::daftarPasienRanap()
            ->orderBy('no_rawat')
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.perawatan.daftar-pasien-ranap')
            ->extends('layouts.admin', ['title' => 'Daftar Pasien Rawat Inap'])
            ->section('content');
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        //
    }

    public function batalkanRanapPasien(string $noRawat, string $tglMasuk, string $jamMasuk, string $kamar)
    {
        $kamarInap = RawatInap::query()
            ->where([
                ['no_rawat', '=', $noRawat],
                ['tgl_masuk', '=', $tglMasuk],
                ['jam_masuk', '=', $jamMasuk],
                ['kd_kamar', '=', $kamar]
            ])
            ->delete();

        Kamar::find($kamar)->update(['status' => 'KOSONG']);

        if (! RawatInap::where('no_rawat', $noRawat)->exists()) {
            RegistrasiPasien::find($noRawat)->update([
                'status_lanjut' => 'Ralan',
                'stts' => 'Sudah',
            ]);
        }

        $this->flashSuccess("Data pasien dengan No. Rawat {$noRawat} sudah kembali ke rawat jalan!");
    }
}
// 2022/11/10/000886
// 2022-11-10
// 23:14:10
// 222D

// admin kartika
// 287044
// 2022/12/14/000891