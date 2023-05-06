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
use Illuminate\Support\Facades\DB;
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
        if ($this->isDeferred || !auth()->user()->can('keuangan.account-payable.read-medis')) {
            return [];
        }

        return PemesananObat::query()
            ->hutangAging($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'detail_titip_faktur.no_tagihan',
                'pemesanan.no_order',
                'pemesanan.no_faktur',
                'datasuplier.nama_suplier',
                'pemesanan.status',
                "ifnull(bayar_pemesanan.nama_bayar, '-')",
                "ifnull(bayar_pemesanan.keterangan, '-')",
            ])
            ->sortWithColumns($this->sortColumns, [
                'tgl_tagihan'   => 'titip_faktur.tanggal',
                'tgl_terima'    => 'pemesanan.tgl_pesan',
                'tagihan'       => DB::raw('round(pemesanan.tagihan, 2)'),
                'dibayar'       => DB::raw('round(bayar_pemesanan.besar_bayar, 2)'),
                'sisa'          => DB::raw('round(pemesanan.tagihan - ifnull(bayar_pemesanan.besar_bayar, 0), 2)'),
                'periode_0_30'  => DB::raw("datediff('{$this->tglAkhir}', titip_faktur.tanggal) <= 30"),
                'periode_31_60' => DB::raw("datediff('{$this->tglAkhir}', titip_faktur.tanggal) between 31 and 60"),
                'periode_61_90' => DB::raw("datediff('{$this->tglAkhir}', titip_faktur.tanggal) between 61 and 90"),
                'periode_90_up' => DB::raw("datediff('{$this->tglAkhir}', titip_faktur.tanggal) > 90"),
            ])
            ->paginate($this->perpage, ['*'], 'page_medis');
    }

    public function getDataAccountPayableNonMedisProperty()
    {
        if ($this->isDeferred || !auth()->user()->can('keuangan.account-payable.read-nonmedis')) {
            return [];
        }

        return PemesananBarangNonMedis::query()
            ->hutangAging($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'ipsrs_detail_titip_faktur.no_tagihan',
                'ipsrspemesanan.no_order',
                'ipsrspemesanan.no_faktur',
                'ipsrssuplier.nama_suplier',
                'ipsrspemesanan.status',
                "ifnull(bayar_pemesanan_non_medis.nama_bayar, '-')",
                "ifnull(bayar_pemesanan_non_medis.keterangan, '-')",
            ])
            ->sortWithColumns($this->sortColumns, [
                'tgl_tagihan'   => 'ipsrs_titip_faktur.tanggal',
                'tgl_terima'    => 'ipsrspemesanan.tgl_pesan',
                'tagihan'       => DB::raw('round(ipsrspemesanan.tagihan, 2)'),
                'dibayar'       => DB::raw('round(bayar_pemesanan_non_medis.besar_bayar, 2)'),
                'sisa'          => DB::raw('round(ipsrspemesanan.tagihan - ifnull(bayar_pemesanan_non_medis.besar_bayar, 0), 2)'),
                'periode_0_30'  => DB::raw("datediff('{$this->tglAkhir}', ipsrs_titip_faktur.tanggal) <= 30"),
                'periode_31_60' => DB::raw("datediff('{$this->tglAkhir}', ipsrs_titip_faktur.tanggal) between 31 and 60"),
                'periode_61_90' => DB::raw("datediff('{$this->tglAkhir}', ipsrs_titip_faktur.tanggal) between 61 and 90"),
                'periode_90_up' => DB::raw("datediff('{$this->tglAkhir}', ipsrs_titip_faktur.tanggal) > 90"),
            ])
            ->paginate($this->perpage, ['*'], 'page_nonmedis');
    }

    public function getTotalAccountPayableMedisProperty()
    {
        if ($this->isDeferred || !auth()->user()->can('keuangan.account-payable.read-medis')) {
            return [];
        }

        $total = PemesananObat::query()
            ->totalHutangAging($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'detail_titip_faktur.no_tagihan',
                'pemesanan.no_order',
                'pemesanan.no_faktur',
                'datasuplier.nama_suplier',
                'pemesanan.status',
                'bayar_pemesanan.nama_bayar',
                'bayar_pemesanan.keterangan',
            ])
            ->get();

        $totalTagihan = (float) $total->sum('total_tagihan');
        $totalDibayar = (float) $total->sum('total_dibayar');
        $totalSisaPerPeriode = $total->pluck('sisa_tagihan', 'periode');
        $totalSisaTagihan = (float) $totalSisaPerPeriode->sum();

        return compact('totalTagihan', 'totalDibayar', 'totalSisaTagihan', 'totalSisaPerPeriode');
    }

    public function getTotalAccountPayableNonMedisProperty()
    {
        if ($this->isDeferred || !auth()->user()->can('keuangan.account-payable.read-nonmedis')) {
            return [];
        }

        $total = PemesananBarangNonMedis::query()
            ->totalHutangAging($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'ipsrs_detail_titip_faktur.no_tagihan',
                'ipsrspemesanan.no_order',
                'ipsrspemesanan.no_faktur',
                'ipsrssuplier.nama_suplier',
                'ipsrspemesanan.status',
                'bayar_pemesanan_non_medis.nama_bayar',
                'bayar_pemesanan_non_medis.keterangan',
            ])
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
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
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
