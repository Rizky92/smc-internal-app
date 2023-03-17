<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Inventaris\SuratPemesananObat;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class PerbandinganBarangPO extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $tglAwal;

    public $tglAkhir;

    public $hanyaTampilkanBarangSelisih;

    protected function queryString()
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'hanyaTampilkanBarangSelisih' => ['except' => false, 'as' => 'barang_selisih'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getPerbandinganOrderObatPOProperty()
    {
        return SuratPemesananObat::query()
            ->perbandinganPemesananObatPO($this->tglAwal, $this->tglAkhir, $this->hanyaTampilkanBarangSelisih)
            ->search($this->cari, [
                "surat_pemesanan_medis.no_pemesanan",
                "databarang.nama_brng",
                "datasuplier.nama_suplier",
                "ifnull(pemesanan_datang.nama_suplier, '-')",
            ])
            ->sortWithColumns($this->sortColumns, [
                'suplier_pesan' => "datasuplier.nama_suplier",
                'suplier_datang' => DB::raw("ifnull(pemesanan_datang.nama_suplier, '-')"),
                'jumlah_pesan' => "detail_surat_pemesanan_medis.jumlah2",
                'jumlah_datang' => DB::raw("ifnull(pemesanan_datang.jumlah, 0)"),
                'selisih' => DB::raw("ifnull((detail_surat_pemesanan_medis.jumlah2 - pemesanan_datang.jumlah), 'Barang belum datang')"),
            ])
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
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->hanyaTampilkanBarangSelisih = false;
    }

    protected function dataPerSheet(): array
    {
        return [
            SuratPemesananObat::query()
                ->perbandinganPemesananObatPO($this->tglAwal, $this->tglAkhir, $this->hanyaTampilkanBarangSelisih)
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
