<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\PenerimaanObat;
use App\Models\Logistik\PemesananBarangNonMedis;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class AccountPayable extends Component
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

    public function getDataAccountPayableMedisProperty()
    {
        if ($this->isDeferred || user()->cannot('keuangan.account-payable.read-medis')) {
            return [];
        }

        return PenerimaanObat::query()
            ->hutangAging($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_medis');
    }

    /**
     * @return Paginator|array<empty, empty>
     */
    public function getDataAccountPayableNonMedisProperty()
    {
        if ($this->isDeferred || user()->cannot('keuangan.account-payable.read-nonmedis')) {
            return [];
        }

        return PemesananBarangNonMedis::query()
            ->hutangAging($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage, ['*'], 'page_nonmedis');
    }

    public function getTotalAccountPayableMedisProperty(): array
    {
        if ($this->isDeferred || user()->cannot('keuangan.account-payable.read-medis')) {
            return [];
        }

        $total = PenerimaanObat::query()
            ->totalHutangAging($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->get();

        $totalTagihan = (float) $total->sum('total_tagihan');
        $totalDibayar = (float) $total->sum('total_dibayar');
        $totalSisaPerPeriode = $total->pluck('sisa_tagihan', 'periode');
        $totalSisaTagihan = (float) $totalSisaPerPeriode->sum();

        return compact('totalTagihan', 'totalDibayar', 'totalSisaTagihan', 'totalSisaPerPeriode');
    }

    public function getTotalAccountPayableNonMedisProperty(): array
    {
        if ($this->isDeferred || user()->cannot('keuangan.account-payable.read-nonmedis')) {
            return [];
        }

        $total = PemesananBarangNonMedis::query()
            ->totalHutangAging($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->get();

        $totalTagihan = (float) $total->sum('total_tagihan');
        $totalDibayar = (float) $total->sum('total_dibayar');
        $totalSisaPerPeriode = $total->pluck('sisa_tagihan', 'periode');
        $totalSisaTagihan = (float) $totalSisaPerPeriode->sum();

        return compact('totalTagihan', 'totalDibayar', 'totalSisaTagihan', 'totalSisaPerPeriode');
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.account-payable')
            ->layout(BaseLayout::class, ['title' => 'Hutang Aging (Account Payable)']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
    }

    protected function dataPerSheet(): array
    {
        $export = [];

        if (user()->can('keuangan.account-payable.read-medis')) {
            $totalMedis = PenerimaanObat::query()
                ->totalHutangAging($this->tglAwal, $this->tglAkhir)
                ->get();

            $totalTagihanMedis = (float) $totalMedis->sum('total_tagihan');
            $totalDibayarMedis = (float) $totalMedis->sum('total_dibayar');
            $totalSisaPerPeriodeMedis = $totalMedis->pluck('sisa_tagihan', 'periode');
            $totalSisaDibayarMedis = (float) $totalSisaPerPeriodeMedis->sum();

            $export['Medis'] = fn () => PenerimaanObat::query()
                ->hutangAging($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (PenerimaanObat $model): array => [
                    'no_tagihan'    => $model->no_tagihan,
                    'no_order'      => $model->no_order,
                    'no_faktur'     => $model->no_faktur,
                    'nama_suplier'  => $model->nama_suplier,
                    'tgl_tagihan'   => $model->tgl_tagihan,
                    'tgl_tempo'     => $model->tgl_tempo,
                    'tgl_terima'    => $model->tgl_terima,
                    'tgl_bayar'     => $model->tgl_bayar,
                    'status'        => $model->status,
                    'nama_bayar'    => $model->nama_bayar,
                    'tagihan'       => floatval($model->tagihan),
                    'dibayar'       => floatval($model->dibayar),
                    'sisa'          => floatval($model->sisa),
                    'periode_0_30'  => $model->umur_hari <= 30 ? floatval($model->sisa) : 0,
                    'periode_31_60' => $model->umur_hari > 30 && $model->umur_hari <= 60 ? floatval($model->sisa) : 0,
                    'periode_61_90' => $model->umur_hari > 60 && $model->umur_hari <= 90 ? floatval($model->sisa) : 0,
                    'periode_90_up' => $model->umur_hari > 90 ? floatval($model->sisa) : 0,
                    'umur_hari'     => intval($model->umur_hari),
                    'keterangan'    => $model->keterangan,
                ])
                ->merge([[
                    'no_tagihan'    => '',
                    'no_order'      => '',
                    'no_faktur'     => '',
                    'nama_suplier'  => '',
                    'tgl_tagihan'   => '',
                    'tgl_tempo'     => '',
                    'tgl_terima'    => '',
                    'tgl_bayar'     => '',
                    'status'        => '',
                    'nama_bayar'    => 'TOTAL',
                    'tagihan'       => $totalTagihanMedis,
                    'dibayar'       => $totalDibayarMedis,
                    'sisa'          => $totalSisaDibayarMedis,
                    'periode_0_30'  => (float) $totalSisaPerPeriodeMedis->get('periode_0_30'),
                    'periode_31_60' => (float) $totalSisaPerPeriodeMedis->get('periode_31_60'),
                    'periode_61_90' => (float) $totalSisaPerPeriodeMedis->get('periode_61_90'),
                    'periode_90_up' => (float) $totalSisaPerPeriodeMedis->get('periode_90_up'),
                    'umur_hari'     => '',
                    'keterangan'    => '',
                ]]);
        }

        if (user()->can('keuangan.account-payable.read-nonmedis')) {
            $totalNonMedis = PemesananBarangNonMedis::query()
                ->totalHutangAging($this->tglAwal, $this->tglAkhir)
                ->get();

            $totalTagihanNonMedis = (float) $totalNonMedis->sum('total_tagihan');
            $totalDibayarNonMedis = (float) $totalNonMedis->sum('total_dibayar');
            $totalSisaPerPeriodeNonMedis = $totalNonMedis->pluck('sisa_tagihan', 'periode');
            $totalSisaDibayarNonMedis = (float) $totalSisaPerPeriodeNonMedis->sum();

            $export['Non Medis'] = fn () => PemesananBarangNonMedis::query()
                ->hutangAging($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (PemesananBarangNonMedis $model): array => [
                    'no_tagihan'    => $model->no_tagihan,
                    'no_order'      => $model->no_order,
                    'no_faktur'     => $model->no_faktur,
                    'nama_suplier'  => $model->nama_suplier,
                    'tgl_tagihan'   => $model->tgl_tagihan,
                    'tgl_tempo'     => $model->tgl_tempo,
                    'tgl_terima'    => $model->tgl_terima,
                    'tgl_bayar'     => $model->tgl_bayar,
                    'status'        => $model->status,
                    'nama_bayar'    => $model->nama_bayar,
                    'tagihan'       => floatval($model->tagihan),
                    'dibayar'       => floatval($model->dibayar),
                    'sisa'          => floatval($model->sisa),
                    'periode_0_30'  => $model->umur_hari <= 30 ? floatval($model->sisa) : 0,
                    'periode_31_60' => $model->umur_hari > 30 && $model->umur_hari <= 60 ? floatval($model->sisa) : 0,
                    'periode_61_90' => $model->umur_hari > 60 && $model->umur_hari <= 90 ? floatval($model->sisa) : 0,
                    'periode_90_up' => $model->umur_hari > 90 ? floatval($model->sisa) : 0,
                    'umur_hari'     => intval($model->umur_hari),
                    'keterangan'    => $model->keterangan,
                ])
                ->merge([[
                    'no_tagihan'    => '',
                    'no_order'      => '',
                    'no_faktur'     => '',
                    'nama_suplier'  => '',
                    'tgl_tagihan'   => '',
                    'tgl_tempo'     => '',
                    'tgl_terima'    => '',
                    'tgl_bayar'     => '',
                    'status'        => '',
                    'nama_bayar'    => 'TOTAL',
                    'tagihan'       => $totalTagihanNonMedis,
                    'dibayar'       => $totalDibayarNonMedis,
                    'sisa'          => $totalSisaDibayarNonMedis,
                    'periode_0_30'  => (float) $totalSisaPerPeriodeNonMedis->get('periode_0_30'),
                    'periode_31_60' => (float) $totalSisaPerPeriodeNonMedis->get('periode_31_60'),
                    'periode_61_90' => (float) $totalSisaPerPeriodeNonMedis->get('periode_61_90'),
                    'periode_90_up' => (float) $totalSisaPerPeriodeNonMedis->get('periode_90_up'),
                    'umur_hari'     => '',
                    'keterangan'    => '',
                ]]);
        }

        return $export;
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Tagihan',
            'No. Order',
            'No. Faktur',
            'Nama Suplier',
            'Tgl. Tagihan',
            'Tgl. Tempo',
            'Tgl. Terima',
            'Tgl. Bayar',
            'Status Penerimaan',
            'Akun Bayar',
            'Jumlah Tagihan',
            'Dibayar',
            'Sisa',
            '0 - 30',
            '31 - 60',
            '61 - 90',
            '> 90',
            'Umur Hari',
            'Keterangan',
        ];
    }

    protected function pageHeaders(): array
    {
        $appends = [];

        if (user()->can('keuangan.account-payable.read-medis')) {
            $appends[] = 'Medis';
        }

        if (user()->can('keuangan.account-payable.read-nonmedis')) {
            $appends[] = 'Non Medis';
        }

        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Hutang Aging (Account Payable) '.collect($appends)->join(', ', ' dan '),
            'Per '.carbon($this->tglAkhir)->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
