<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\Jurnal\JurnalMedis;
use App\Models\Keuangan\Jurnal\JurnalNonMedis;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class JurnalSupplierPO extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getJurnalBarangMedisProperty()
    {
        return $this->isDeferred ? [] : JurnalMedis::query()
            ->jurnalPenerimaanBarang($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_medis');
    }

    public function getJurnalBarangNonMedisProperty()
    {
        return $this->isDeferred ? [] : JurnalNonMedis::query()
            ->jurnalPenerimaanBarang($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_nonmedis');
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.jurnal-supplier-po')
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
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
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
            'Medis' => fn () => JurnalMedis::query()
                ->jurnalPenerimaanBarang($this->tglAwal, $this->tglAkhir)
                ->cursor()
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

            'Non Medis' => fn () => JurnalNonMedis::query()
                ->jurnalPenerimaanBarang($this->tglAwal, $this->tglAkhir)
                ->cursor()
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

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

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
