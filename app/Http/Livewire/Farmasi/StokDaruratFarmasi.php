<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Obat;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Vtiful\Kernel\Excel;

class StokDaruratFarmasi extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

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
        $this->perpage = 25;
        $this->cari = '';
    }

    public function getStokDaruratObatProperty()
    {
        return Obat::daruratStok(Str::lower($this->cari))
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.stok-darurat-farmasi')
            ->layout(BaseLayout::class, ['title' => 'Darurat Stok']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "excel/{$timestamp}_farmasi_daruratstok.xlsx";

        $config = [
            'path' => storage_path('app/public'),
        ];

        $row1 = 'RS Samarinda Medika Citra';
        $row2 = 'Laporan Darurat Stok Farmasi';
        $row3 = now()->format('d F Y');

        $columnHeaders = [
            'Kode',
            'Nama',
            'Satuan kecil',
            'Kategori',
            'Stok minimal',
            'Stok saat ini',
            'Saran order',
            'Supplier',
            'Harga Per Unit (Rp)',
            'Total Harga (Rp)',
        ];

        $data = Obat::daruratStok()
            ->cursor()
            ->toArray();

        (new Excel($config))
            ->fileName($filename)

            // page header
            ->mergeCells('A1:J1', $row1)
            ->mergeCells('A2:J2', $row2)
            ->mergeCells('A3:J3', $row3)

            // column header
            ->insertText(3, 0, $columnHeaders[0])
            ->insertText(3, 1, $columnHeaders[1])
            ->insertText(3, 2, $columnHeaders[2])
            ->insertText(3, 3, $columnHeaders[3])
            ->insertText(3, 4, $columnHeaders[4])
            ->insertText(3, 5, $columnHeaders[5])
            ->insertText(3, 6, $columnHeaders[6])
            ->insertText(3, 7, $columnHeaders[7])
            ->insertText(3, 8, $columnHeaders[8])
            ->insertText(3, 9, $columnHeaders[9])
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
        $this->perpage = 25;
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
