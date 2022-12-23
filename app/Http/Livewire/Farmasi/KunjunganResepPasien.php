<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepDokter;
use App\Models\Farmasi\ResepDokterRacikan;
use App\Support\Excel\ExcelExport;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;
use Vtiful\Kernel\Format;

class KunjunganResepPasien extends Component
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

    public function getKunjunganResepObatRegularPasienProperty()
    {
        return ResepDokter::kunjunganResepObatRegular(
            $this->periodeAwal,
            $this->periodeAkhir,
            $this->jenisPerawatan
        )
            ->paginate($this->perpage, ['*'], $this::OBAT_REGULAR_PAGE);
    }

    public function getKunjunganResepObatRacikanPasienProperty()
    {
        return ResepDokterRacikan::kunjunganResepObatRacikan(
            $this->periodeAwal,
            $this->periodeAkhir,
            $this->jenisPerawatan
        )
            ->paginate($this->perpage, ['*'], $this::OBAT_RACIKAN_PAGE);
    }

    public function getColumnHeadersProperty()
    {
        return [
            'no_resep' => 'No. Resep',
            'nm_dokter' => 'Dokter Peresep',
            'tgl_perawatan' => 'Tgl. Validasi',
            'jam' => 'Jam',
            'nm_pasien' => 'Pasien',
            'status_lanjut' => 'Jenis Perawatan',
            'total' => 'Total Pembelian (RP)',
        ];
    }

    public function render()
    {
        return view('livewire.farmasi.kunjungan-resep-pasien')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien Per Bentuk Obat']);
    }

    public function searchData()
    {
        $this->resetPage($this::OBAT_REGULAR_PAGE);
        $this->resetPage($this::OBAT_RACIKAN_PAGE);

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

        $filename = "excel/{$timestamp}_farmasi_kunjungan_resep.xlsx";

        $sheet1 = ResepDokter::kunjunganResepObatRegular($this->periodeAwal, $this->periodeAkhir, $this->jenisPerawatan)->get()->toArray();
        $sheet2 = ResepDokterRacikan::kunjunganResepObatRacikan($this->periodeAwal, $this->periodeAkhir, $this->jenisPerawatan)->get()->toArray();

        $titles = [
            'RS Samarinda Medika Citra',
            'Laporan Kunjungan Resep Pasien',
            now()->format('d F Y'),
        ];

        $excel = (new ExcelExport($filename, 'Obat Regular'))
            ->setPageHeaders($titles)
            ->setColumnHeaders($this->columnHeaders)
            ->setData($sheet1);

        $excel->useSheet('Obat Racikan')
            ->setData($sheet2);

        return $excel->export();
    }
}
