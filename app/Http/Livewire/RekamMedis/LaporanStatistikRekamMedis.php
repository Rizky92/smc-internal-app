<?php

namespace App\Http\Livewire\RekamMedis;

use App\Models\RekamMedis\StatistikRekamMedis;
use App\Support\Traits\Livewire\FlashComponent;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Vtiful\Kernel\Excel;

class LaporanStatistikRekamMedis extends Component
{
    use WithPagination, FlashComponent;

    public $periodeAwal;

    public $periodeAkhir;

    public $cari;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
        'resetFilters',
        'fullRefresh',
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
            'page' => [
                'except' => 1,
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

    public function getDataLaporanStatistikProperty()
    {
        return StatistikRekamMedis::whereBetween('tgl_registrasi', [$this->periodeAwal, $this->periodeAkhir])
            ->orderBy('no_rawat')
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.rekam-medis.laporan-statistik-rekam-medis')
            ->extends('layouts.admin', ['title' => 'Laporan Statistik'])
            ->section('content');
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "excel/{$timestamp}_rekammedis.xlsx";

        $config = [
            'path' => storage_path('app/public'),
        ];

        $columnHeaders = [
            'No. Rawat',
            'No. RM',
            'Nama Pasien',
            'NIK',
            'L / P',
            'Tgl. Lahir',
            'Umur',
            'Agama',
            'Suku',
            'Jenis Perawatan',
            'Pasien Lama / Baru',
            'Status',
            'Tgl. Masuk',
            'Jam Masuk',
            'Tgl. Pulang',
            'Jam Pulang',
            'Diagnosa Masuk',
            'ICD Diagnosa',
            'Diagnosa',
            'ICD Tindakan',
            'Tindakan',
            'DPJP',
            'Poli',
            'Kelas',
            'Penjamin',
            'Status Bayar',
            'Status Pulang',
            'No. HP',
            'Alamat',
            'Kunjungan ke',
        ];

        $data = StatistikRekamMedis::whereBetween('tgl_registrasi', [$this->periodeAwal, $this->periodeAkhir])
            ->orderBy('no_rawat')
            ->cursor()
            ->toArray();

        (new Excel($config))
            ->fileName($filename)
            ->header($columnHeaders)
            ->data($data)
            ->output();

        return Storage::disk('public')->download($filename);
    }

    public function resetFilters()
    {
        $this->cari = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
        $this->perpage = 25;

        $this->emit('$refresh');
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->emit('resetFilters');
    }
}
