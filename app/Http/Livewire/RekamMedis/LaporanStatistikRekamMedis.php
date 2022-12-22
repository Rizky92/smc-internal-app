<?php

namespace App\Http\Livewire\RekamMedis;

use App\Models\RekamMedis\StatistikRekamMedis;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
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

    protected function getColumnHeaders()
    {
        return [
            'no_rawat'         => 'No. Rawat',
            'no_rkm_medis'     => 'No. RM',
            'nm_pasien'        => 'Nama Pasien',
            'no_ktp'           => 'NIK',
            'jk'               => 'L / P',
            'tgl_lahir'        => 'Tgl. Lahir',
            'umur'             => 'Umur',
            'agama'            => 'Agama',
            'nama_suku_bangsa' => 'Suku',
            'status_lanjut'    => 'Jenis Perawatan',
            'status_poli'      => 'Pasien Lama / Baru',
            'status_perawatan' => 'Status Ralan',
            'tgl_registrasi'   => 'Tgl. Masuk',
            'jam_reg'          => 'Jam Masuk',
            'tgl_keluar'       => 'Tgl. Pulang',
            'jam_keluar'       => 'Jam Pulang',
            'diagnosa_awal'    => 'Diagnosa Masuk',
            'kd_diagnosa'      => 'ICD Diagnosa',
            'nm_diagnosa'      => 'Diagnosa',
            'kd_tindakan'      => 'ICD Tindakan',
            'nm_tindakan'      => 'Tindakan',
            'lama_operasi'     => 'Lama Operasi',
            'rujukan_masuk'    => 'Rujukan Masuk',
            'nm_dokter'        => 'DPJP',
            'nm_poli'          => 'Poli',
            'kelas'            => 'Kelas',
            'png_jawab'        => 'Penjamin',
            'status_bayar'     => 'Status Bayar',
            'stts_pulang'      => 'Status Pulang',
            'rujuk_ke_rs'      => 'Rujuk Keluar ke RS',
            'no_tlp'           => 'No. HP',
            'alamat'           => 'Alamat',
            'kunjungan_ke'     => 'Kunjungan ke',
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
        return StatistikRekamMedis::query()
            ->denganPencarian($this->cari)
            ->whereBetween('tgl_registrasi', [$this->periodeAwal, $this->periodeAkhir])
            ->orderBy('no_rawat')
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.rekam-medis.laporan-statistik-rekam-medis')
            ->layout(BaseLayout::class, ['title' => 'Laporan Statistik']);
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
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
            'Status Ralan',
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
