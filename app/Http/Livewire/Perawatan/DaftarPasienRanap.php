<?php

namespace App\Http\Livewire\Perawatan;

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

    public $cari;

    public $perpage;

    public $tglAwal;

    public $tglAkhir;

    public $jamAwal;

    public $jamAkhir;

    public $statusPerawatan;

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
            'statusPerawatan' => [
                'except' => '-',
                'as' => 'status_perawatan'
            ],
            'tglAwal' => [
                'except' => now()->format('Y-m-d'),
                'as' => 'tgl_awal',
            ],
            'tglAkhir' => [
                'except' => now()->format('Y-m-d'),
                'as' => 'tgl_akhir',
            ],
            'jamAwal' => [
                'except' => RegistrasiPasien::JAM_AWAL,
                'as' => 'jam_awal',
            ],
            'jamAkhir' => [
                'except' => RegistrasiPasien::JAM_AKHIR,
                'as' => 'jam_akhir',
            ],
        ];
    }

    private function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->statusPerawatan = '-';
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
        $this->jamAwal = RegistrasiPasien::JAM_AWAL;
        $this->jamAkhir = RegistrasiPasien::JAM_AKHIR;
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDaftarPasienRanapProperty()
    {
        return RegistrasiPasien::daftarPasienRanap(
            $this->cari,
            $this->statusPerawatan,
            $this->tglAwal,
            $this->tglAkhir,
            $this->jamAwal,
            $this->jamAkhir
        )
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

        $filename = "{$timestamp}_daftar_pasien_ranap";

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
        $this->defaultValues();

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
