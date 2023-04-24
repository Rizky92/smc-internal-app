<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Farmasi\Inventaris\PemesananObat;
use App\Models\Logistik\PemesananBarangNonMedis;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Livewire\Component;

class AccountPayable extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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

    public function getDataAccountPayableMedisProperty()
    {
        return $this->isDeferred
            ? []
            : PemesananObat::query()
                ->hutangAging($this->tglAwal, $this->tglAkhir)
                ->paginate($this->perpage, ['*'], 'page_medis');
    }

    public function getDataAccountPayableNonMedisProperty()
    {
        return $this->isDeferred
            ? []
            : PemesananBarangNonMedis::query()
                ->hutangAging($this->tglAwal, $this->tglAkhir)
                ->paginate($this->perpage, ['*'], 'page_nonmedis');
    }

    public function getTotalAccountPayableMedisProperty()
    {
        if ($this->isDeferred)
            return [];

        $total = PemesananObat::query()
            ->totalHutangAging($this->tglAwal, $this->tglAkhir)
            ->get();

        $totalTagihan = (float) $total->sum('total_tagihan');
        $totalDibayar = (float) $total->sum('total_dibayar');
        $totalSisaPerPeriode = $total->pluck('sisa_tagihan', 'periode');
        $totalSisaTagihan = (float) $totalSisaPerPeriode->sum();

        return compact('totalTagihan', 'totalDibayar', 'totalSisaTagihan', 'totalSisaPerPeriode');
    }

    public function getTotalAccountPayableNonMedisProperty()
    {
        if ($this->isDeferred)
            return [];

        $total = PemesananBarangNonMedis::query()
            ->totalHutangAging($this->tglAwal, $this->tglAkhir)
            ->get();

        $totalTagihan = (float) $total->sum('total_tagihan');
        $totalDibayar = (float) $total->sum('total_dibayar');
        $totalSisaPerPeriode = $total->pluck('sisa_tagihan', 'periode');
        $totalSisaTagihan = (float) $totalSisaPerPeriode->sum();

        return compact('totalTagihan', 'totalDibayar', 'totalSisaTagihan', 'totalSisaPerPeriode');
    }

    public function render()
    {
        return view('livewire.keuangan.account-payable')
            ->layout(BaseLayout::class, ['title' => 'Hutang Aging (Account Payable)']);
    }

    protected function defaultValues()
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        $export = [];

        if (auth()->user()->can('keuangan.account-payable.read-medis')) {
            $totalMedis = PemesananObat::query()
                ->totalHutangAging($this->tglAwal, $this->tglAkhir)
                ->get();

            $totalTagihanMedis = (float) $totalMedis->sum('total_tagihan');
            $totalDibayarMedis = (float) $totalMedis->sum('total_dibayar');
            $totalSisaPerPeriodeMedis = $totalMedis->pluck('sisa_tagihan', 'periode');
            $totalSisaDibayarMedis = (float) $totalSisaPerPeriodeMedis->sum();

            $export['Medis'] = PemesananObat::query()
                ->hutangAging($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (PemesananObat $model) => [
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
                    'tagihan'       => $model->tagihan,
                    'dibayar'       => $model->dibayar,
                    'sisa'          => $model->sisa,
                    'periode_0_30'  => $model->umur_hari <= 30 ? $model->sisa : 0,
                    'periode_31_60' => $model->umur_hari > 30 && $model->umur_hari <= 60 ? $model->sisa : 0,
                    'periode_61_90' => $model->umur_hari > 60 && $model->umur_hari <= 90 ? $model->sisa : 0,
                    'periode_90_up' => $model->umur_hari > 90 ? $model->sisa : 0,
                    'umur_hari'     => $model->umur_hari,
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
                    'periode_0_30'  => $totalSisaPerPeriodeMedis->get('periode_0_30') ?? 0,
                    'periode_31_60' => $totalSisaPerPeriodeMedis->get('periode_31_60') ?? 0,
                    'periode_61_90' => $totalSisaPerPeriodeMedis->get('periode_61_90') ?? 0,
                    'periode_90_up' => $totalSisaPerPeriodeMedis->get('periode_90_up') ?? 0,
                    'umur_hari'     => '',
                    'keterangan'    => '',
                ]]);
        }

        if (auth()->user()->can('keuangan.account-payable.read-nonmedis')) {
            $totalNonMedis = PemesananBarangNonMedis::query()
                ->totalHutangAging($this->tglAwal, $this->tglAkhir)
                ->get();

            $totalTagihanNonMedis = (float) $totalNonMedis->sum('total_tagihan');
            $totalDibayarNonMedis = (float) $totalNonMedis->sum('total_dibayar');
            $totalSisaPerPeriodeNonMedis = $totalNonMedis->pluck('sisa_tagihan', 'periode');
            $totalSisaDibayarNonMedis = (float) $totalSisaPerPeriodeNonMedis->sum();

            $export['Non Medis'] = PemesananBarangNonMedis::query()
                ->hutangAging($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (PemesananBarangNonMedis $model) => [
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
                    'tagihan'       => $model->tagihan,
                    'dibayar'       => $model->dibayar,
                    'sisa'          => $model->sisa,
                    'periode_0_30'  => $model->umur_hari <= 30 ? $model->sisa : 0,
                    'periode_31_60' => $model->umur_hari > 30 && $model->umur_hari <= 60 ? $model->sisa : 0,
                    'periode_61_90' => $model->umur_hari > 60 && $model->umur_hari <= 90 ? $model->sisa : 0,
                    'periode_90_up' => $model->umur_hari > 90 ? $model->sisa : 0,
                    'umur_hari'     => $model->umur_hari,
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
                    'periode_0_30'  => $totalSisaPerPeriodeNonMedis->get('periode_0_30') ?? 0,
                    'periode_31_60' => $totalSisaPerPeriodeNonMedis->get('periode_31_60') ?? 0,
                    'periode_61_90' => $totalSisaPerPeriodeNonMedis->get('periode_61_90') ?? 0,
                    'periode_90_up' => $totalSisaPerPeriodeNonMedis->get('periode_90_up') ?? 0,
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
            'Keterangan',
        ];
    }

    protected function pageHeaders(): array
    {
        $appends = [];

        if (auth()->user()->can('keuangan.account-payable.read-medis')) {
            $appends[] = 'Medis';
        }

        if (auth()->user()->can('keuangan.account-payable.read-nonmedis')) {
            $appends[] = 'Non Medis';
        }

        return [
            'RS Samarinda Medika Citra',
            'Hutang Aging (Account Payable) ' . collect($appends)->join(', ', ' dan '),
            'Per ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
