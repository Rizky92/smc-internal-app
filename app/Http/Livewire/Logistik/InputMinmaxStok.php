<?php

namespace App\Http\Livewire\Logistik;

use App\Models\Logistik\BarangNonMedis;
use App\Models\Logistik\MinmaxStokBarangNonMedis;
use App\Models\Logistik\SupplierNonMedis;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class InputMinmaxStok extends Component
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
        'searchData',
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
        return view('livewire.logistik.input-minmax-stok')
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

        $filename = "{$timestamp}_logistik_stokminmax_barang.xlsx";

        $titles = [
            'RS Samarinda Medika Citra',
            'Minmax Stok Barang Non Medis',
            now()->format('d F Y'),
        ];

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

        $excel = ExcelExport::make($filename)
            ->setPageHeaders($titles)
            ->setColumnHeaders($columnHeaders)
            ->setData($data);

        return $excel->export();
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

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
