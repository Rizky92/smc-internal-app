<?php

namespace App\Http\Livewire\Perawatan;

use App\Models\Perawatan\Kamar;
use App\Models\Perawatan\RawatInap;
use App\Models\Perawatan\RegistrasiPasien;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;

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
        return view('livewire.perawatan.daftar-pasien-ranap')
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
