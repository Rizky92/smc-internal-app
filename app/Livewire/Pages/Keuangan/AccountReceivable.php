<?php

namespace App\Livewire\Pages\Keuangan;

use App\Jobs\Keuangan\BayarPiutangPasien;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\AkunBayar;
use App\Models\Keuangan\PenagihanPiutang;
use App\Models\RekamMedis\Penjamin;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class AccountReceivable extends Component
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

    /** @var string */
    public $jaminanPasien;

    /** @var string */
    public $jenisPerawatan;

    /** @var string */
    public $tglBayar;

    /** @var string */
    public $rekeningAkun;

    /** @var array */
    public $tagihanDipilih;

    /** @var int|float */
    public $totalDibayar;

    /** @var bool */
    public $bedaJaminan;

    protected function queryString(): array
    {
        return [
            'tglAwal'        => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'       => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'jaminanPasien'  => ['except' => '-', 'as' => 'jaminan_pasien'],
            'jenisPerawatan' => ['except' => 'semua', 'as' => 'jenis_perawatan'],
            'bedaJaminan'    => ['except' => false, 'as' => 'beda_jaminan'],
        ];
    }

    public function mount(): void
    {
        $this->tagihanDipilih = [];
        $this->totalDibayar = 0;

        $this->defaultValues();
    }

    /**
     * @return Paginator|array<empty, empty>
     */
    public function getDataAccountReceivableProperty()
    {
        return $this->isDeferred ? [] : PenagihanPiutang::query()
            ->accountReceivable($this->tglAwal, $this->tglAkhir, $this->jaminanPasien, $this->jenisPerawatan, $this->bedaJaminan)
            ->search($this->cari)
            ->accountReceivableDipilih($this->tagihanDipilih)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function getPenjaminProperty(): Collection
    {
        return Penjamin::where('status', '1')->pluck('png_jawab', 'kd_pj');
    }

    public function getAkunBayarProperty(): Collection
    {
        return AkunBayar::pluck('kd_rek', 'nama_bayar')->prepend('-', '-');
    }

    public function getDataTotalAccountReceivableProperty(): array
    {
        if ($this->isDeferred) {
            return [];
        }

        $total = PenagihanPiutang::query()
            ->totalAccountReceivable($this->tglAwal, $this->tglAkhir, $this->jaminanPasien, $this->jenisPerawatan)
            ->search($this->cari)
            ->when(! empty($this->tagihanDipilih), fn (Builder $q): Builder => $q->orWhereIn(
                DB::raw("concat_ws('_', penagihan_piutang.no_tagihan, penagihan_piutang.kd_pj, detail_penagihan_piutang.no_rawat)"),
                array_keys($this->tagihanDipilih)
            ))
            ->get();

        $totalDiskonPiutang = collect($this->tagihanDipilih)
            ->filter(fn (array $value): bool => isset($value['selected']) && $value['selected'])
            ->map(fn (array $value): array => [
                'selected'       => isset($value['selected']) ? $value['selected'] : false,
                'diskon_piutang' => empty($value['diskon_piutang']) ? 0 : $value['diskon_piutang'],
            ])
            ->sum('diskon_piutang');

        $totalPiutang = (float) $total->sum('total_piutang');
        $totalCicilan = (float) $total->sum('total_cicilan');
        $totalSisaPerPeriode = $total->pluck('sisa_piutang', 'periode');
        $totalSisaCicilan = (float) $totalSisaPerPeriode->sum();

        return compact('totalPiutang', 'totalCicilan', 'totalSisaPerPeriode', 'totalSisaCicilan', 'totalDiskonPiutang');
    }

    public function render(): View
    {
        $this->rekalkulasiPembayaran();

        $this->tagihanDipilih = collect($this->tagihanDipilih)
            ->reject(fn (array $v): bool => isset($v['selected']) && ! $v['selected'])
            ->all();

        return view('livewire.pages.keuangan.account-receivable')
            ->layout(BaseLayout::class, ['title' => 'Piutang Aging (Account Receivable)']);
    }

    protected function rekalkulasiPembayaran(): void
    {
        $tagihanDipilih = collect($this->tagihanDipilih)
            ->filter(fn (array $value): bool => isset($value['selected']) && $value['selected']);

        $diskonPiutang = $tagihanDipilih
            ->map(fn (array $value): array => [
                'selected'          => isset($value['selected']) ? $value['selected'] : false,
                'diskon_piutang'    => empty($value['diskon_piutang']) ? 0 : $value['diskon_piutang'],
            ])
            ->sum('diskon_piutang');

        $this->totalDibayar = PenagihanPiutang::query()
            ->accountReceivable($this->tglAwal, $this->tglAkhir, $this->jaminanPasien, $this->jenisPerawatan)
            ->whereIn(
                DB::raw("concat_ws('_', penagihan_piutang.no_tagihan, penagihan_piutang.kd_pj, detail_penagihan_piutang.no_rawat)"),
                $tagihanDipilih->keys()->all()
            )
            ->sum(DB::raw('round(detail_piutang_pasien.totalpiutang - ifnull(bayar_piutang.besar_cicilan, 0), 2)'));

        $this->totalDibayar -= $diskonPiutang;
    }

    public function pilihSemua(bool $pilih): void
    {
        if (! $pilih) {
            $this->tagihanDipilih = [];
            $this->totalDibayar = 0;

            return;
        }

        $query = PenagihanPiutang::query()
            ->accountReceivable($this->tglAwal, $this->tglAkhir, $this->jaminanPasien, $this->jenisPerawatan, $this->bedaJaminan)
            ->search($this->cari);

        $this->tagihanDipilih = $query
            ->cursor(['no_tagihan', 'kd_pj', 'no_rawat'])
            ->mapWithKeys(fn (PenagihanPiutang $model, $_): array => [
                implode('_', [$model->no_tagihan, $model->kd_pj_tagihan, $model->no_rawat]) => [
                    'selected'       => true,
                    'diskon_piutang' => $model->diskon ?? 0,
                ],
            ])
            ->all();

        $this->rekalkulasiPembayaran();
    }

    public function validasiPiutang(): void
    {
        if (user()->cannot('keuangan.account-receivable.validasi-piutang')) {
            $this->flashError('Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        if ($this->rekeningAkun === '-') {
            $this->flashError('Silahkan pilih "Akun Pembayaran" terlebih dahulu!');

            return;
        }

        $akunLainnya = DB::connection('mysql_sik')->table('set_akun');

        $akunDiskonPiutang = $akunLainnya->value('Diskon_Piutang');
        $akunTidakTerbayar = $akunLainnya->value('Piutang_Tidak_Terbayar');

        collect($this->tagihanDipilih)
            ->filter(fn (array $value): bool => $value['selected'])
            ->map(fn (array $value): array => [
                'diskon_piutang' => $value['diskon_piutang'] ?? 0,
            ])
            ->each(function (array $value, string $key) use ($akunDiskonPiutang, $akunTidakTerbayar) {
                BayarPiutangPasien::dispatch([
                    'key'                 => $key,
                    'diskon_piutang'      => $value['diskon_piutang'],
                    'tgl_awal'            => $this->tglAwal,
                    'tgl_akhir'           => $this->tglAkhir,
                    'jaminan_pasien'      => $this->jaminanPasien,
                    'jenis_perawatan'     => $this->jenisPerawatan,
                    'tgl_bayar'           => $this->tglBayar,
                    'user_id'             => user()->nik,
                    'akun'                => $this->akunBayar->get($this->rekeningAkun),
                    'akun_diskon_piutang' => $akunDiskonPiutang,
                    'akun_tidak_terbayar' => $akunTidakTerbayar,
                ]);
            });

        $this->tagihanDipilih = [];
        $this->totalDibayar = 0;
        $this->dispatchBrowserEvent('clear-selected');

        $this->flashInfo('Validasi piutang sedang diproses!');
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();

        $this->bedaJaminan = false;
        $this->rekeningAkun = '-';
        $this->jaminanPasien = '-';
        $this->jenisPerawatan = 'semua';
        $this->tglBayar = now()->toDateString();
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        $total = PenagihanPiutang::query()
            ->totalAccountReceivable($this->tglAwal, $this->tglAkhir, $this->jaminanPasien, $this->jenisPerawatan)
            ->get();

        $totalPiutang = (float) $total->sum('total_piutang');
        $totalCicilan = (float) $total->sum('total_cicilan');
        $totalSisaPerPeriode = $total->pluck('sisa_piutang', 'periode');
        $totalSisaCicilan = (float) $totalSisaPerPeriode->sum();

        return [
            fn () => PenagihanPiutang::query()
                ->accountReceivable($this->tglAwal, $this->tglAkhir, $this->jaminanPasien, $this->jenisPerawatan, $this->bedaJaminan)
                ->search($this->cari)
                ->cursor()
                ->map(fn (PenagihanPiutang $model) => [
                    'no_tagihan'         => $model->no_tagihan,
                    'no_rawat'           => $model->no_rawat,
                    'nm_pasien'          => $model->nm_pasien,
                    'total_piutang'      => floatval($model->total_piutang),
                    'besar_cicilan'      => floatval($model->besar_cicilan),
                    'sisa_piutang'       => floatval($model->sisa_piutang),
                    'penjab_pasien'      => $model->penjab_pasien,
                    'penjab_tagihan'     => $model->penjab_tagihan,
                    'catatan'            => $model->catatan,
                    'status'             => $model->status,
                    'nama_bayar'         => $model->nama_bayar,
                    'no_rkm_medis'       => $model->no_rkm_medis,
                    'periode_0_30'       => $model->umur_hari <= 30 ? floatval($model->sisa_piutang) : 0,
                    'periode_31_60'      => $model->umur_hari > 30 && $model->umur_hari <= 60 ? floatval($model->sisa_piutang) : 0,
                    'periode_61_90'      => $model->umur_hari > 60 && $model->umur_hari <= 90 ? floatval($model->sisa_piutang) : 0,
                    'periode_90_up'      => $model->umur_hari > 90 ? floatval($model->sisa_piutang) : 0,
                    'umur_hari'          => intval($model->umur_hari),
                    'rekening_penagihan' => $model->kd_rek_tagihan.' '.$model->nama_bank,
                    'tgl_tagihan'        => $model->tgl_tagihan,
                    'tgl_jatuh_tempo'    => $model->tgl_jatuh_tempo,
                    'tgl_bayar'          => $model->tgl_bayar ?? '-',
                ])
                ->merge([[
                    'no_tagihan'         => '',
                    'no_rawat'           => '',
                    'nm_pasien'          => 'TOTAL',
                    'total_piutang'      => $totalPiutang,
                    'besar_cicilan'      => $totalCicilan,
                    'sisa_piutang'       => $totalSisaCicilan,
                    'penjab_pasien'      => '',
                    'penjab_tagihan'     => '',
                    'catatan'            => '',
                    'status'             => '',
                    'nama_bayar'         => '',
                    'no_rkm_medis'       => '',
                    'periode_0_30'       => (float) $totalSisaPerPeriode->get('periode_0_30'),
                    'periode_31_60'      => (float) $totalSisaPerPeriode->get('periode_31_60'),
                    'periode_61_90'      => (float) $totalSisaPerPeriode->get('periode_61_90'),
                    'periode_90_up'      => (float) $totalSisaPerPeriode->get('periode_90_up'),
                    'umur_hari'          => '',
                    'rekening_penagihan' => '',
                    'tgl_tagihan'        => '',
                    'tgl_jatuh_tempo'    => '',
                    'tgl_bayar'          => '',
                ]]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Tagihan',
            'No. Rawat',
            'Pasien',
            'Piutang',
            'Cicilan',
            'Sisa',
            'Jaminan Pasien',
            'Jaminan Akun Piutang',
            'Catatan',
            'Status Piutang',
            'Nama Bayar',
            'No RM',
            '0 - 30',
            '31 - 60',
            '61 - 90',
            '> 90',
            'Umur Hari',
            'Rekening Tagihan',
            'Tgl. Tagihan',
            'Tgl. Jatuh Tempo',
            'Tgl. Bayar',
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
            'Piutang Aging (Account Receivable)',
            'Per '.carbon($this->tglAkhir)->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
