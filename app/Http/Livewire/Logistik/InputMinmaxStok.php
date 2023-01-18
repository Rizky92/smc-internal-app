<?php

namespace App\Http\Livewire\Logistik;

use App\Models\Logistik\BarangNonMedis;
use App\Models\Logistik\MinmaxStokBarangNonMedis;
use App\Models\Logistik\SupplierNonMedis;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class InputMinmaxStok extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable;

    public $cari;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'stokMin' => ['required', 'numeric', 'min:0'],
        'stokMax' => ['required', 'numeric', 'min:0'],
        'kodeSupplier' => ['required', 'string'],
    ];

    protected function queryString()
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
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

    public function simpan(string $kodeBarang, int $stokMin = 0, int $stokMax = 0, $kodeSupplier)
    {
        if (! auth()->user()->can('logistik.stok-minmax.update')) {
            $this->flashError('Anda tidak memiliki izin untuk mengupdate barang');

            return;
        }

        $kodeSupplier = $kodeSupplier != '' ? $kodeSupplier : null;

        tracker_start('mysql_smc');

        MinmaxStokBarangNonMedis::updateOrCreate([
            'kode_brng' => $kodeBarang,
        ], [
            'stok_min' => $stokMin,
            'stok_max' => $stokMax,
            'kode_suplier' => $kodeSupplier,
        ]);

        tracker_end('mysql_smc');

        $this->resetFilters();
        $this->dispatchBrowserEvent('data-tersimpan');

        $this->flashSuccess('Data berhasil disimpan!');
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
    }

    protected function dataPerSheet(): array
    {
        return [
            BarangNonMedis::denganMinmax('', true)->get()
        ];
    }

    protected function columnHeaders(): array
    {
        return [
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
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Minmax Stok Barang Non Medis',
            now()->format('d F Y'),
        ];
    }
}
