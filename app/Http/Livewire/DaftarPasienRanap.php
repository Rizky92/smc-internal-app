<?php

namespace App\Http\Livewire;

use App\Models\Perawatan\Kamar;
use App\Models\Perawatan\RawatInap;
use App\Models\Perawatan\RegistrasiPasien;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class DaftarPasienRanap extends Component
{
    use WithPagination, FlashComponent;

    private const BELUM_PULANG = 'belum pulang';
    private const SUDAH_PULANG = 'sudah pulang';
    private const BERDASARKAN_TGL_MASUK = 'berdasarkan tgl masuk';
    private const BERDASARKAN_TGL_PULANG = 'berdasarkan tgl pulang';

    public $cari;

    public $perpage;

    public $periodeAwal;

    public $periodeAkhir;

    public $jenisRanapDitampilkan;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
        'searchData',
        'resetFilters',
        'fullRefresh',
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
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenisRanapDitampilkan = self::BELUM_PULANG;
    }

    public function getDaftarPasienRanapProperty()
    {
        return RegistrasiPasien::daftarPasienRanap($this->jenisRanapDitampilkan, $this->cari)
            ->orderBy('no_rawat')
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.daftar-pasien-ranap')
            ->layout(BaseLayout::class, ['title' => 'Daftar Pasien Rawat Inap']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "{$timestamp}_perawatan_daftar_pasien_ranap";

        $titles = [
            'RS Samarinda Medika Citra',
            'Daftar Pasien Rawat Inap',
            Carbon::parse($this->periodeAwal)->format('d F Y') . ' - ' . Carbon::parse($this->periodeAkhir)->format('d F Y'),
        ];

        $columnHeaders = [
            'No. Rawat',
            'No. RM',
            'Pasien',
            'Alamat',
            'Agama',
            'P.J.',
            'Jenis Bayar',
            'Kamar',
            'Tarif',
            'Tgl. Masuk',
            'Jam Masuk',
            'Lama Inap',
            'Dokter P.J.',
            'No. HP',
        ];

        $data = RegistrasiPasien::daftarPasienRanap($this->jenisRanapDitampilkan)
            ->orderBy('no_rawat')
            ->get();

        $excel = ExcelExport::make($filename)
            ->setPageHeaders($titles)
            ->setColumnHeaders($columnHeaders)
            ->setData($data);
        
        return $excel->export();
    }

    public function batalkanRanapPasien(string $noRawat, string $tglMasuk, string $jamMasuk, string $kamar)
    {
        if (!auth()->user()->can('perawatan.rawat-inap.batal-ranap')) {
            $this->flashError('Anda tidak dapat melakukan aksi ini');

            return;
        }

        RawatInap::where([
            ['no_rawat', '=', $noRawat],
            ['tgl_masuk', '=', $tglMasuk],
            ['jam_masuk', '=', $jamMasuk],
            ['kd_kamar', '=', $kamar]
        ])
            ->delete();

        Kamar::find($kamar)->update(['status' => 'KOSONG']);

        if (!RawatInap::where('no_rawat', $noRawat)->exists()) {
            RegistrasiPasien::find($noRawat)->update([
                'status_lanjut' => 'Ralan',
                'stts' => 'Sudah',
            ]);
        }

        $this->flashSuccess("Data pasien dengan No. Rawat {$noRawat} sudah kembali ke rawat jalan!");
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->cari = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenisRanapDitampilkan = self::BELUM_PULANG;

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
