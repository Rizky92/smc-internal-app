<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepObat;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Vtiful\Kernel\Excel;

class PenggunaanObatPerdokter extends Component
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

    protected function queryString(): array
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
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->perpage = 25;
    }

    public function getObatPerDokterProperty()
    {
        return ResepObat::query()
            ->penggunaanObatPerDokter($this->periodeAwal, $this->periodeAkhir, Str::lower($this->cari))
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.penggunaan-obat-perdokter')
            ->layout(BaseLayout::class, ['title' => 'Penggunaan Obat Per Dokter Peresep']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "excel/{$timestamp}_obat_perdokter.xlsx";

        $config = [
            'path' => storage_path('app/public'),
        ];

        $headerTglAwal = Carbon::parse($this->periodeAwal)->format('d F Y');
        $headerTglAkhir = Carbon::parse($this->periodeAkhir)->format('d F Y');

        $row1 = 'RS Samarinda Medika Citra';
        $row2 = 'Laporan Penggunaan Obat Per Dokter Peresep';
        $row3 = "{$headerTglAwal} - {$headerTglAkhir}";

        $columnHeaders = [
            'No. Resep',
            'Tgl. Validasi',
            'Jam',
            'Nama Obat',
            'Jumlah',
            'Dokter Peresep',
            'Asal',
            'Asal Poli',
        ];

        $data = ResepObat::penggunaanObatPerDokter($this->periodeAwal, $this->periodeAkhir)
            ->cursor()
            ->toArray();

        (new Excel($config))
            ->fileName($filename)

            // page header
            ->mergeCells('A1:H1', $row1)
            ->mergeCells('A2:H2', $row2)
            ->mergeCells('A3:H3', $row3)

            // column header
            ->insertText(3, 0, $columnHeaders[0])
            ->insertText(3, 1, $columnHeaders[1])
            ->insertText(3, 2, $columnHeaders[2])
            ->insertText(3, 3, $columnHeaders[3])
            ->insertText(3, 4, $columnHeaders[4])
            ->insertText(3, 5, $columnHeaders[5])
            ->insertText(3, 6, $columnHeaders[6])
            ->insertText(3, 7, $columnHeaders[7])
            ->insertText(4, 0, '')

            // insert data
            ->data($data)
            ->output();

        return Storage::disk('public')->download($filename);
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
        $this->resetPage();
        $this->perpage = 25;

        $this->emit('$refresh');
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
