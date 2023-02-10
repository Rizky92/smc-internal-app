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
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class PenarikanDataSuplierPO extends Component
{
    use WithPagination, FlashComponent, Filterable, LiveTable, MenuTracker;

    public $periodeAwal;

    public $periodeAkhir;

    protected $listeners = [
        //
    ];

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
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
            ->layout(BaseLayout::class, ['title' => 'Penarikan Data Suplier Penerimaan Barang Medis / Non Medis']);
    }

    public function tarikDataTerbaru()
    {
        JurnalMedis::refreshModel();
        
        JurnalNonMedis::refreshModel();

        $this->fullRefresh();

        $this->flashSuccess('Data Berhasil Diperbaharui!');
    }

    public function exportToExcel()
    {
        // Patch untuk menggantikan trait ExcelExportable

        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $filename = Str::of(class_basename($this))
            ->snake()
            ->prepend(now()->format('Ymd_His_'))
            ->trim();

        $excel = ExcelExport::make((string) $filename, 'Medis')
            ->setPageHeaders([
                'RS Samarinda Medika Citra',
                'Penarikan Data Supplier PO Medis/Non Medis',
                now()->format('d F Y'),
                Carbon::parse($this->periodeAwal)->format('d F Y') . ' - ' . Carbon::parse($this->periodeAkhir)->format('d F Y'),
            ])
            ->setColumnHeaders([
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
            ])
            ->setData(JurnalMedis::jurnalPenerimaanBarang($this->periodeAwal, $this->periodeAkhir)->get()->toArray())
            ->addSheet('Non Medis')
            ->setData(JurnalNonMedis::jurnalPenerimaanBarang($this->periodeAwal, $this->periodeAkhir)->get()->toArray());

        return $excel->export();
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    // protected function dataPerSheet(): array
    // {
    //     return [
    //         'Obat/BHP/Alkes' => JurnalMedis::jurnalPenerimaanBarang($this->periodeAwal, $this->periodeAkhir)->get()->toArray(),
    //         'Non Medis' => JurnalNonMedis::jurnalPenerimaanBarang($this->periodeAwal, $this->periodeAkhir)->get()->toArray(),
    //     ];
    // }

    // protected function columnHeaders(): array
    // {
    //     return [
    //         '#',
    //         'No. Jurnal',
    //         'Waktu',
    //         'No. Faktur',
    //         'Keterangan',
    //         'Status',
    //         'Nominal (Rp)',
    //         'Akun Bayar',
    //         'Kode Rekening',
    //         'Nama Rekening',
    //         'Supplier',
    //         'Nama Pegawai',
    //     ];
    // }

    // protected function pageHeaders(): array
    // {
    //     return [
    //         'RS Samarinda Medika Citra',
    //         'Penarikan Data Supplier PO Medis/Non Medis',
    //         // now()->format('d F Y'),
    //         Carbon::parse($this->periodeAwal)->format('d F Y') . ' - ' . Carbon::parse($this->periodeAkhir)->format('d F Y'),
    //     ];
    // }
}
