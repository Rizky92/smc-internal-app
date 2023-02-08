<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\Jurnal\JurnalMedis;
use App\Models\Keuangan\Jurnal\JurnalNonMedis;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class PenarikanDataSuplierPO extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'periodeAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getJurnalBarangMedisProperty()
    {
        return JurnalMedis::query()
            ->jurnalPenerimaanBarang($this->periodeAwal, $this->periodeAkhir)
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
                'nm_pegawai' => "trim(concat(jurnal_medis.nik, ' ', coalesce(pegawai.nama, '')))"
            ])
            ->paginate($this->perpage);
    }

    public function getJurnalBarangNonMedisProperty()
    {
        return JurnalNonMedis::query()
            ->jurnalPenerimaanBarang($this->periodeAwal, $this->periodeAkhir)
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
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.keuangan.penarikan-data-suplier-p-o')
            ->layout(BaseLayout::class, ['title' => 'Penarikan Data Suplier Penerimaan Barang']);
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->format('Y-m-d');
        $this->periodeAkhir = now()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Obat/BHP/Alkes' => JurnalMedis::jurnalPenerimaanBarang($this->periodeAwal, $this->periodeAkhir)->get(),
            'Non Medis' => JurnalNonMedis::jurnalPenerimaanBarang($this->periodeAwal, $this->periodeAkhir)->get(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            '#',
            'No. Jurnal',
            'Waktu',
            'Statement',
            'Status',
            'Nominal',
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
            Carbon::parse($this->periodeAwal)->format('d F Y') . ' - ' . Carbon::parse($this->periodeAkhir)->format('d F Y'),
        ];
    }
}
