<?php

namespace App\Http\Livewire\RekamMedis;

use App\Models\RekamMedis\DemografiPasien;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanDemografiPasien extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

    public $periodeAwal;

    public $periodeAkhir;

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
    }

    public function getColumnHeadersProperty()
    {
        // 0 - 28 HR
        // 28 HR - 1 THN
        // 1-4 THN
        // 5-14 THN
        // 15-24 THN
        // 25-54 THN
        // 45-64 THN
        // >65 THN
        return [
            'kecamatan' => 'Nama Kecamatan',
            'no_rm' => 'No RM',
            'no_rawat' => 'No Registrasi',
            'nm_pasien' => 'Nama Pasien',
            'almt' => 'Alamat',
            'umur' => 'Rentang Umur',
            'jk' => 'Jenis Kelamin',
            'diagnosa' => 'Diagnosa',
            'agama' => 'Agama',
            'pendidikan' => 'Pendidikan',
            'bahasa' => 'Bahasa',
            'suku' => 'Suku',
        ];
    }

    public function getDemografiPasienProperty()
    {
        return DemografiPasien::paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.rekam-medis.laporan-demografi-pasien')
            ->layout(BaseLayout::class, ['title' => 'Demografi Pasien']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        
    }
}
