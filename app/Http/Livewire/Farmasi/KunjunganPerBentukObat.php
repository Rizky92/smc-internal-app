<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepDokter;
use App\Models\Farmasi\ResepDokterRacikan;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class KunjunganPerBentukObat extends Component
{
    use WithPagination, FlashComponent;

    private const OBAT_REGULAR_PAGE = 'obat_regular_page';
    private const OBAT_RACIKAN_PAGE = 'obat_racikan_page';

    public $perpage;

    public $periodeAwal;

    public $periodeAkhir;

    public $jenisPerawatan;

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
            'perpage' => [
                'except' => 25,
            ],
            'periodeAwal' => [
                'except' => now()->startOfMonth()->format('Y-m-d'),
                'as' => 'periode_awal',
            ],
            'periodeAkhir' => [
                'except' => now()->endOfMonth()->format('Y-m-d'),
                'as' => 'periode_akhir'
            ],
            'jenisPerawatan' => [
                'except' => '',
                'as' => 'jenis_perawatan',
            ],
        ];
    }

    public function mount()
    {
        $this->perpage = 25;
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenisPerawatan = '';
    }

    public function render()
    {
        return view('livewire.farmasi.kunjungan-per-bentuk-obat')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien Per Bentuk Obat']);
    }

    public function getKunjunganResepObatRegularPasienProperty()
    {
        return ResepDokter::kunjunganResepObatRegular(
            $this->periodeAwal,
            $this->periodeAkhir,
            $this->jenisPerawatan
        )
            ->paginate($this->perpage, ['*'], self::OBAT_REGULAR_PAGE);
    }

    public function getKunjunganResepObatRacikanPasienProperty()
    {
        return ResepDokterRacikan::kunjunganResepObatRacikan(
            $this->periodeAwal,
            $this->periodeAkhir,
            $this->jenisPerawatan
        )
            ->paginate($this->perpage, ['*'], self::OBAT_RACIKAN_PAGE);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "{$timestamp}_farmasi_kunjungan_resep_pasien_per_bentuk_obat.xlsx";

        $sheet1 = ResepDokter::kunjunganResepObatRegular($this->periodeAwal, $this->periodeAkhir, $this->jenisPerawatan)->get()->toArray();
        $sheet2 = ResepDokterRacikan::kunjunganResepObatRacikan($this->periodeAwal, $this->periodeAkhir, $this->jenisPerawatan)->get()->toArray();

        $titles = [
            'RS Samarinda Medika Citra',
            'Laporan Kunjungan Resep Pasien',
            now()->format('d F Y'),
        ];

        $columnHeaders = [
            'No. Resep',
            'Dokter Peresep',
            'Tgl. Validasi',
            'Jam',
            'Pasien',
            'Jenis Perawatan',
            'Total Pembelian (RP)',
        ];

        $excel = ExcelExport::make($filename, 'Obat Regular')
            ->setPageHeaders($titles)
            ->setColumnHeaders($columnHeaders)
            ->setData($sheet1);

        $excel->addSheet('Obat Racikan')
            ->setData($sheet2);

        return $excel->export();
    }

    public function searchData()
    {
        $this->resetPage(self::OBAT_REGULAR_PAGE);
        $this->resetPage(self::OBAT_RACIKAN_PAGE);

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->perpage = 25;
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jenisPerawatan = '';
        
        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
