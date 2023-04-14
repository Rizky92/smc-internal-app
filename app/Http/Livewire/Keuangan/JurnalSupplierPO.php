<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\Jurnal\JurnalMedis;
use App\Models\Keuangan\Jurnal\JurnalNonMedis;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use DB;
use Livewire\Component;
use Livewire\WithPagination;

class JurnalSupplierPO extends Component
{
    use WithPagination, FlashComponent, Filterable, LiveTable, MenuTracker, ExcelExportable;

    public $tglAwal;

    public $tglAkhir;

    protected function queryString()
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getJurnalBarangMedisProperty()
    {
        return JurnalMedis::query()
            ->jurnalPenerimaanBarang($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                "jurnal_medis.id",
                "jurnal_medis.no_jurnal",
                "jurnal_medis.waktu_jurnal",
                "jurnal_medis.no_faktur",
                "jurnal_medis.ket",
                "jurnal_medis.status",
                "bayar_pemesanan.besar_bayar",
                "bayar_pemesanan.nama_bayar",
                "rekening.kd_rek",
                "rekening.nm_rek",
                "datasuplier.nama_suplier",
                "jurnal_medis.nik",
                "pegawai.nama",
            ])
            ->sortWithColumns($this->sortColumns, [
                'nm_pegawai' => DB::raw("trim(concat(jurnal_medis.nik, ' ', coalesce(pegawai.nama, '')))"),
            ])
            ->paginate($this->perpage, ['*'], 'page_medis');
    }

    public function getJurnalBarangNonMedisProperty()
    {
        return JurnalNonMedis::query()
            ->jurnalPenerimaanBarang($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'jurnal_non_medis.id',
                'jurnal_non_medis.no_jurnal',
                'jurnal_non_medis.waktu_jurnal',
                'jurnal_non_medis.no_faktur',
                'jurnal_non_medis.ket',
                'jurnal_non_medis.status',
                'bayar_pemesanan_non_medis.besar_bayar',
                'bayar_pemesanan_non_medis.nama_bayar',
                'rekening.kd_rek',
                'rekening.nm_rek',
                'ipsrssuplier.nama_suplier',
                "jurnal_non_medis.nik",
                "pegawai.nama",
            ])
            ->sortWithColumns($this->sortColumns, [
                'nm_pegawai' => "trim(concat(jurnal_non_medis.nik, ' ', coalesce(pegawai.nama, '')))"
            ])
            ->paginate($this->perpage, ['*'], 'page_nonmedis');
    }

    public function render()
    {
        return view('livewire.keuangan.jurnal-supplier-p-o')
            ->layout(BaseLayout::class, ['title' => 'Penarikan Data Suplier Penerimaan Barang Medis / Non Medis dari Jurnal']);
    }

    public function tarikDataTerbaru()
    {
        JurnalMedis::refreshModel();

        JurnalNonMedis::refreshModel();

        $this->fullRefresh();

        $this->flashSuccess('Data Berhasil Diperbaharui!');
    }

    protected function defaultValues()
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    public function searchData()
    {
        $this->resetPage('page_medis');
        $this->resetPage('page_nonmedis');

        $this->emit('$refresh');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Medis' => JurnalMedis::jurnalPenerimaanBarang($this->tglAwal, $this->tglAkhir)->get(),
            'Non Medis' => JurnalNonMedis::jurnalPenerimaanBarang($this->tglAwal, $this->tglAkhir)->get(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            '#',
            'No. Jurnal',
            'Waktu',
            'No. Faktur',
            'Keterangan',
            'Status',
            'Nominal (Rp)',
            'Akun Bayar',
            'Kode Rekening',
            'Nama Rekening',
            'Supplier',
            'Nama Pegawai',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Penarikan Data Supplier PO Medis/Non Medis',
            now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
