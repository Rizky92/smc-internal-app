<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Inventaris\SuratPemesananObat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class PerbandinganBarangPO extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable;

    public $cari;

    public $periodeAwal;

    public $periodeAkhir;

    public $perpage;

    public $hanyaTampilkanBarangSelisih;

    protected $paginationTheme = 'bootstrap';

    protected function queryString()
    {
        return [
            'cari' => ['except' => ''],
            'perpage' => ['except' => 25],
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'periode_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'periode_akhir'],
            'hanyaTampilkanBarangSelisih' => ['except' => false, 'as' => 'barang_selisih'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getPerbandinganOrderObatPOProperty()
    {
        return SuratPemesananObat::perbandinganPemesananObatPO(
            $this->periodeAwal,
            $this->periodeAkhir,
            Str::lower($this->cari),
            $this->hanyaTampilkanBarangSelisih
        )
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.perbandingan-barang-p-o')
            ->layout(BaseLayout::class, ['title' => 'Ringkasan Perbandingan Barang PO Farmasi']);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->hanyaTampilkanBarangSelisih = false;
    }

    protected function dataPerSheet(): array
    {
        return [
            SuratPemesananObat::query()
                ->perbandinganPemesananObatPO($this->periodeAwal, $this->periodeAkhir, '', $this->hanyaTampilkanBarangSelisih)
                ->get()
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Pemesanan',
            'Nama',
            'Supplier Tujuan',
            'Supplier yang Mendatangkan',
            'Jumlah Dipesan',
            'Jumlah yang Datang',
            'Selisih',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Ringkasan Perbandingan PO Obat',
            now()->format('d F Y'),
        ];
    }
}
