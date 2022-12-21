<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepDokter;
use App\Models\Farmasi\ResepDokterRacikan;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Vtiful\Kernel\Excel;

class KunjunganResepPasien extends Component
{
    use WithPagination, FlashComponent;

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
            ->paginate($this->perpage, ['*'], 'obat_regular_page');
    }

    public function getKunjunganResepObatRacikanPasienProperty()
    {
        return ResepDokterRacikan::kunjunganResepObatRacikan(
            $this->periodeAwal,
            $this->periodeAkhir,
            $this->jenisPerawatan
        )
            ->paginate($this->perpage, ['*'], 'obat_racikan_page');
    }

    public function render()
    {
        return view('livewire.farmasi.kunjungan-resep-pasien')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien']);
    }

    public function searchData()
    {
        // dd($this->jenisPerawatan);
        $this->resetPage('obat_racikan_page');
        $this->resetPage('obat_regular_page');

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

        $config = [
            'path' => storage_path('app/public'),
        ];

        $row1 = 'RS Samarinda Medika Citra';
        $row2 = 'Laporan Darurat Stok Farmasi';
        $row3 = now()->format('d F Y');

        $columnHeaders = [
            'No. Resep',
            'Dokter Peresep',
            'Tgl. Validasi',
            'Jam',
            'Pasien',
            'Jenis Perawatan',
            'Total Pembelian (RP)',
        ];

        $sheet1 = ResepDokter::kunjunganResepObatRegular($this->periodeAwal, $this->periodeAkhir, $this->jenisPerawatan)->get()->toArray();
        $sheet2 = ResepDokterRacikan::kunjunganResepObatRacikan($this->periodeAwal, $this->periodeAkhir, $this->jenisPerawatan)->get()->toArray();

        $excel = new Excel($config);
        $excel->fileName($filename, 'Obat Regular')
            ->mergeCells('A1:G1', $row1)
            ->mergeCells('A2:G2', $row2)
            ->mergeCells('A3:G3', $row3);

        foreach ($columnHeaders as $idx => $header) {
            $excel->insertText(3, $idx, $header);
        }

        $excel->insertText(4, 0, '');
        $excel->data($sheet1);

        $excel->addSheet('Obat Racikan')
            ->mergeCells('A1:G1', $row1)
            ->mergeCells('A2:G2', $row2)
            ->mergeCells('A3:G3', $row3);

        foreach ($columnHeaders as $idx => $header) {
            $excel->insertText(3, $idx, $header);
        }

        $excel->insertText(4, 0, '');
        $excel->data($sheet2);

        $excel->output();

        return Storage::disk('public')->download($filename);
    }
}
