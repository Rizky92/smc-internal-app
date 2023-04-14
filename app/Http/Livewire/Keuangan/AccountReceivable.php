<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\PenagihanPiutangDetail;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AccountReceivable extends Component
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

    public function getDataAccountReceivableProperty()
    {
        return $this->isDeferred
            ? collect()
            : PenagihanPiutangDetail::query()
                ->tagihanPiutangAging($this->tglAwal, $this->tglAkhir)
                ->search($this->cari, [
                    'detail_penagihan_piutang.no_tagihan',
                    'detail_penagihan_piutang.no_rawat',
                    'reg_periksa.no_rkm_medis',
                    'pasien.nm_pasien',
                    'penjab_pasien.png_jawab',
                    'penjab_tagihan.png_jawab',
                    'penagihan_piutang.catatan',
                    'detail_piutang_pasien.nama_bayar',
                ])
                ->sortWithColumns($this->sortColumns, [
                    'tgl_tagihan'     => 'penagihan_piutang.tanggal',
                    'tgl_jatuh_tempo' => 'penagihan_piutang.tanggaltempo',
                    'penjab_pasien'   => 'penjab_pasien.png_jawab',
                    'penjab_piutang'  => 'penjab_tagihan.png_jawab',
                    'total_piutang'   => DB::raw('round(detail_piutang_pasien.totalpiutang, 2)'),
                    'besar_cicilan'   => DB::raw('round(bayar_piutang.besar_cicilan, 2)'),
                    'sisa_piutang'    => DB::raw('round(detail_piutang_pasien.totalpiutang - ifnull(bayar_piutang.besar_cicilan, 0), 2)'),
                ])
                ->paginate($this->perpage);
    }

    public function getTotalPiutangAgingProperty()
    {
        
    }

    public function render()
    {
        return view('livewire.keuangan.account-receivable')
            ->layout(BaseLayout::class, ['title' => 'Piutang Aging (Account Receivable)']);
    }

    protected function defaultValues()
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            PenagihanPiutangDetail::query()
                ->tagihanPiutangAging($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (PenagihanPiutangDetail $model) => [
                    'no_tagihan'      => $model->no_tagihan,
                    'no_rawat'        => $model->no_rawat,
                    'tgl_tagihan'     => $model->tgl_tagihan,
                    'tgl_jatuh_tempo' => $model->tgl_jatuh_tempo,
                    'tgl_bayar'       => $model->tgl_bayar,
                    'no_rkm_medis'    => $model->no_rkm_medis,
                    'nm_pasien'       => $model->nm_pasien,
                    'penjab_pasien'   => $model->penjab_pasien,
                    'penjab_piutang'  => $model->penjab_piutang,
                    'catatan'         => $model->catatan,
                    'nama_bayar'      => $model->nama_bayar,
                    'total_piutang'   => $model->total_piutang,
                    'besar_cicilan'   => $model->besar_cicilan,
                    'sisa_piutang'    => $model->sisa_piutang,
                    'periode_0_30'    => $model->umur_hari <= 30 ? $model->sisa_piutang : 0,
                    'periode_31_60'   => $model->umur_hari > 30 && $model->umur_hari <= 60 ? $model->sisa_piutang : 0,
                    'periode_61_90'   => $model->umur_hari > 60 && $model->umur_hari <= 90 ? $model->sisa_piutang : 0,
                    'periode_90_up'   => $model->umur_hari > 90 ? $model->sisa_piutang : 0,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Tagihan',
            'No. Rawat',
            'Tgl. Tagihan',
            'Tgl. Jatuh Tempo',
            'Tgl. Bayar Terakhir',
            'No. RM',
            'Pasien',
            'Asuransi Pasien',
            'Jaminan Piutang',
            'Catatan',
            'Total Piutang',
            'Uang Muka',
            'Cicilan saat ini',
            'Sisa Piutang',
            '0 - 30',
            '31 - 60',
            '61 - 90',
            '> 90',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Piutang Aging per ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->translatedFormat('d F Y') . ' - ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
        ];
    }
}
