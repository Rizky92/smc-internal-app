<?php

namespace App\Http\Livewire\Logistik;

use App\Models\Logistik\BarangNonMedis;
use App\Models\Logistik\MinmaxStokBarangNonMedis;
use App\Models\Logistik\SupplierNonMedis;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Vtiful\Kernel\Excel;

class MinmaxBarang extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

    public $kodeSupplier;

    public $stokMin;

    public $stokMax;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
        'resetFilters',
        'fullRefresh',
    ];

    protected $rules = [
        'stokMin' => ['required', 'numeric', 'min:0'],
        'stokMax' => ['required', 'numeric', 'min:0'],
        'kodeSupplier' => ['required', 'string'],
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
        $this->cari = '';
        $this->page = 1;
        $this->perpage = 25;
    }

    public function getSupplierProperty()
    {
        return SupplierNonMedis::pluck('nama_suplier', 'kode_suplier');
    }

    public function getBarangLogistikProperty()
    {
        return BarangNonMedis::denganMinmax(Str::lower($this->cari))->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.logistik.minmax-barang')
            ->layout(BaseLayout::class, ['title' => 'Stok Minmax Barang Logistik']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function simpan(string $kodeBarang, int $stokMin = 0, int $stokMax = 0, $kodeSupplier)
    {
        if (! auth()->user()->can('logistik.stok-minmax.update')) {
            $this->flashError('Anda tidak memiliki izin untuk mengupdate barang');

            return;
        }

        $kodeSupplier = $kodeSupplier != '-' ? $kodeSupplier : null;

        MinmaxStokBarangNonMedis::updateOrCreate([
            'kode_brng' => $kodeBarang,
        ], [
            'stok_min' => $stokMin,
            'stok_max' => $stokMax,
            'kode_suplier' => $kodeSupplier,
        ]);

        $this->resetFilters();

        $this->flashSuccess('Data berhasil disimpan!');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "excel/{$timestamp}_stokminmax_barang.xlsx";

        $config = [
            'path' => storage_path('app/public'),
        ];

        $row1 = 'RS Samarinda Medika Citra';
        $row2 = 'Minmax Stok Barang Non Medis';
        $row3 = now()->format('d F Y');

        $columnHeaders = [
            'Kode',
            'Nama',
            'Satuan',
            'Jenis',
            'Supplier',
            'Min',
            'Max',
            'Saat ini',
            'Saran order',
            'Harga Per Unit (Rp)',
            'Total Harga (Rp)',
        ];

        $data = BarangNonMedis::denganMinmax($this->cari, true)
            ->cursor()
            ->toArray();

        (new Excel($config))
            ->fileName($filename)

            // page header
            ->mergeCells('A1:K1', $row1)
            ->mergeCells('A2:K2', $row2)
            ->mergeCells('A3:K3', $row3)

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
            ->insertText(3, 10, $columnHeaders[10])

            // empty row untuk insert data
            ->insertText(4, 0, '')

            ->data($data)
            ->output();

        return Storage::disk('public')->download($filename);
    }

    public function resetFilters()
    {
        $this->cari = '';
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
