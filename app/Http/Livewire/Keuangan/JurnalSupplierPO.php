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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class JurnalSupplierPO extends Component
{
    use FlashComponent, Filterable, LiveTable, MenuTracker, ExcelExportable;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getJurnalBarangMedisProperty(): LengthAwarePaginator
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

    public function getJurnalBarangNonMedisProperty(): LengthAwarePaginator
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

    public function render(): View
    {
        return view('livewire.keuangan.jurnal-supplier-p-o')
            ->layout(BaseLayout::class, ['title' => 'Penarikan Data Suplier Penerimaan Barang Medis / Non Medis dari Jurnal']);
    }

    public function tarikDataTerbaru(): void
    {
        JurnalMedis::refreshModel();

        JurnalNonMedis::refreshModel();

        $this->fullRefresh();

        $this->flashSuccess('Data Berhasil Diperbaharui!');
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    public function searchData(): void
    {
        $this->resetPage('page_medis');
        $this->resetPage('page_nonmedis');

        $this->emit('$refresh');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Medis' => JurnalMedis::query()
                ->jurnalPenerimaanBarang($this->tglAwal, $this->tglAkhir)
                ->get()
                ->map(fn (JurnalMedis $model) => [
                    'no_jurnal'    => $model->no_jurnal,
                    'waktu_jurnal' => $model->waktu_jurnal,
                    'no_faktur'    => $model->no_faktur,
                    'ket'          => $model->ket,
                    'status'       => $model->status,
                    'besar_bayar'  => floatval($model->besar_bayar),
                    'nama_bayar'   => $model->nama_bayar,
                    'kd_rek'       => $model->kd_rek,
                    'nm_rek'       => $model->nm_rek,
                    'nama_suplier' => $model->nama_suplier,
                    'nm_pegawai'   => $model->nm_pegawai,
                ]),

            'Non Medis' => JurnalNonMedis::query()
                ->jurnalPenerimaanBarang($this->tglAwal, $this->tglAkhir)
                ->get()
                ->map(fn (JurnalNonMedis $model) => [
                    'no_jurnal'    => $model->no_jurnal,
                    'waktu_jurnal' => $model->waktu_jurnal,
                    'no_faktur'    => $model->no_faktur,
                    'ket'          => $model->ket,
                    'status'       => $model->status,
                    'besar_bayar'  => floatval($model->besar_bayar),
                    'nama_bayar'   => $model->nama_bayar,
                    'kd_rek'       => $model->kd_rek,
                    'nm_rek'       => $model->nm_rek,
                    'nama_suplier' => $model->nama_suplier,
                    'nm_pegawai'   => $model->nm_pegawai,
                ]),
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
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Penarikan Data Supplier PO Medis/Non Medis',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
