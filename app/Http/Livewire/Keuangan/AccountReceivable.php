<?php

namespace App\Http\Livewire\Keuangan;

use App\Jobs\Keuangan\BayarPiutangPasien;
use App\Models\Keuangan\AkunBayar;
use App\Models\Keuangan\BayarPiutang;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\PenagihanPiutang;
use App\Models\Keuangan\PenagihanPiutangDetail;
use App\Models\RekamMedis\Penjamin;
use App\Support\Livewire\Concerns\DeferredLoading;
use App\Support\Livewire\Concerns\ExcelExportable;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\LiveTable;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class AccountReceivable extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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

    protected function queryString(): array
    {
        return [
            'tglAwal'         => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir'        => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'jaminanPasien'   => ['except' => '-', 'as' => 'jaminan_pasien'],
            'jenisPerawatan'  => ['except' => 'semua', 'as' => 'jenis_perawatan'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /** 
     * @return \Illuminate\Contracts\Pagination\Paginator|array<empty, empty>
     */
    public function getDataAccountReceivableProperty()
    {
        return $this->isDeferred
            ? []
            : PenagihanPiutang::query()
            ->accountReceivable($this->tglAwal, $this->tglAkhir, $this->jaminanPasien, $this->jenisPerawatan)
            ->search($this->cari, [
                'detail_penagihan_piutang.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'penjab_tagihan.png_jawab',
                'reg_periksa.kd_pj',
                'penjab_pasien.png_jawab',
                'piutang_pasien.status',
                'akun_piutang.kd_rek',
                'akun_piutang.nama_bayar',
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
            ->search($this->cari, [
                'detail_penagihan_piutang.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'penjab_tagihan.png_jawab',
                'reg_periksa.kd_pj',
                'penjab_pasien.png_jawab',
                'piutang_pasien.status',
                'akun_piutang.kd_rek',
                'akun_piutang.nama_bayar',
            ])
            ->get();

        $totalPiutang = (float) $total->sum('total_piutang');
        $totalCicilan = (float) $total->sum('total_cicilan');
        $totalSisaPerPeriode = $total->pluck('sisa_piutang', 'periode');
        $totalSisaCicilan = (float) $totalSisaPerPeriode->sum();

        return compact('totalPiutang', 'totalCicilan', 'totalSisaPerPeriode', 'totalSisaCicilan');
    }

    public function updatedTagihanDipilih(): void
    {
        $this->rekalkulasiPembayaran();
    }

    public function updatedCari(): void
    {
        $this->tagihanDipilih = [];
        $this->rekalkulasiPembayaran();
    }

    public function hydrate(): void
    {
        $this->rekalkulasiPembayaran();
    }

    public function render(): View
    {
        return view('livewire.keuangan.account-receivable')
            ->layout(BaseLayout::class, ['title' => 'Piutang Aging (Account Receivable)']);
    }

    protected function rekalkulasiPembayaran(): void
    {
        $this->totalDibayar = PenagihanPiutang::query()
            ->join('detail_penagihan_piutang', 'penagihan_piutang.no_tagihan', '=', 'detail_penagihan_piutang.no_tagihan')
            ->whereIn(
                DB::raw('concat(penagihan_piutang.no_tagihan, "_", penagihan_piutang.kd_pj, "_", detail_penagihan_piutang.no_rawat)'),
                collect($this->tagihanDipilih)->filter()->keys()->all()
            )
            ->sum('sisapiutang');
    }

    public function pilihSemua(bool $pilih): void
    {
        if (!$pilih) {
            $this->tagihanDipilih = [];
            $this->totalDibayar = 0;

            return;
        }

        $this->tagihanDipilih = PenagihanPiutang::query()
            ->accountReceivable($this->tglAwal, $this->tglAkhir, $this->jaminanPasien, $this->jenisPerawatan)
            ->search($this->cari, [
                'detail_penagihan_piutang.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'penjab_tagihan.png_jawab',
                'reg_periksa.kd_pj',
                'penjab_pasien.png_jawab',
                'piutang_pasien.status',
                'akun_piutang.kd_rek',
                'akun_piutang.nama_bayar',
            ])
            ->cursor(['no_tagihan', 'kd_pj', 'no_rawat'])
            ->mapWithKeys(fn (PenagihanPiutang $model, $_): array => [
                implode('_', [$model->no_tagihan, $model->kd_pj_tagihan, $model->no_rawat]) => [
                    'selected' => true,
                    'diskon_piutang' => 0,
                ]
            ])
            ->all();

        $this->rekalkulasiPembayaran();
    }

    public function validasiPiutang(): void
    {
        if (!Auth::user()->can('keuangan.account-receivable.validasi-piutang')) {
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
            ->map(function (array $value, string $key): array {
                [$noTagihan, $pjTagihan, $noRawat] = explode('_', $key);

                $diskonPiutang = $value['diskon_piutang'] ?? 0;

                return [
                    'no_tagihan' => $noTagihan,
                    'kd_pj' => $pjTagihan,
                    'no_rawat' => $noRawat,
                    'diskon_piutang' => $diskonPiutang
                ];
            })
            ->values()
            // ->dd();
            ->each(function (array $value) use ($akunDiskonPiutang, $akunTidakTerbayar) {
                BayarPiutangPasien::dispatch([
                    'no_tagihan'          => $value['no_tagihan'],
                    'kd_pj'               => $value['kd_pj'],
                    'no_rawat'            => $value['no_rawat'],
                    'diskon_piutang'      => $value['diskon_piutang'],
                    'tgl_awal'            => $this->tglAwal,
                    'tgl_akhir'           => $this->tglAkhir,
                    'jaminan_pasien'      => $this->jaminanPasien,
                    'jenis_perawatan'     => $this->jenisPerawatan,
                    'tgl_bayar'           => $this->tglBayar,
                    'user_id'             => (string) Auth::user()->nik,
                    'akun'                => (string) $this->akunBayar->get($this->rekeningAkun),
                    'akun_diskon_piutang' => (string) $akunDiskonPiutang,
                    'akun_tidak_terbayar' => (string) $akunTidakTerbayar,
                ]);
            });

        $this->tagihanDipilih = [];
        $this->rekalkulasiPembayaran();
        $this->dispatchBrowserEvent('clear-selected');

        $this->flashInfo('Validasi piutang sedang diproses!');
    }

    protected function defaultValues(): void
    {
        $this->tagihanDipilih = [];
        $this->tglBayar = now()->format('Y-m-d');
        $this->rekeningAkun = '-';

        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->jaminanPasien = '-';
        $this->jenisPerawatan = 'semua';
    }

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
            PenagihanPiutang::query()
                ->accountReceivable($this->tglAwal, $this->tglAkhir, $this->jaminanPasien, $this->jenisPerawatan)
                ->cursor()
                ->map(fn (PenagihanPiutang $model) => [
                    'no_tagihan'      => $model->no_tagihan,
                    'no_rawat'        => $model->no_rawat,
                    'tgl_tagihan'     => $model->tgl_tagihan,
                    'tgl_jatuh_tempo' => $model->tgl_jatuh_tempo,
                    'tgl_bayar'       => $model->tgl_bayar ?? '-',
                    'no_rkm_medis'    => $model->no_rkm_medis,
                    'nm_pasien'       => $model->nm_pasien,
                    'penjab_pasien'   => $model->penjab_pasien,
                    'penjab_tagihan'  => $model->penjab_tagihan,
                    'catatan'         => $model->catatan,
                    'status'          => $model->status,
                    'nama_bayar'      => $model->nama_bayar,
                    'total_piutang'   => floatval($model->total_piutang),
                    'besar_cicilan'   => floatval($model->besar_cicilan),
                    'sisa_piutang'    => floatval($model->sisa_piutang),
                    'periode_0_30'    => $model->umur_hari <= 30 ? floatval($model->sisa_piutang) : 0,
                    'periode_31_60'   => $model->umur_hari > 30 && $model->umur_hari <= 60 ? floatval($model->sisa_piutang) : 0,
                    'periode_61_90'   => $model->umur_hari > 60 && $model->umur_hari <= 90 ? floatval($model->sisa_piutang) : 0,
                    'periode_90_up'   => $model->umur_hari > 90 ? floatval($model->sisa_piutang) : 0,
                    'umur_hari'       => intval($model->umur_hari),
                ])
                ->merge([[
                    'no_tagihan'      => '',
                    'no_rawat'        => '',
                    'tgl_tagihan'     => '',
                    'tgl_jatuh_tempo' => '',
                    'tgl_bayar'       => '',
                    'no_rkm_medis'    => '',
                    'nm_pasien'       => '',
                    'penjab_pasien'   => '',
                    'penjab_tagihan'  => '',
                    'catatan'         => '',
                    'status'          => '',
                    'nama_bayar'      => 'TOTAL',
                    'total_piutang'   => $totalPiutang,
                    'besar_cicilan'   => $totalCicilan,
                    'sisa_piutang'    => $totalSisaCicilan,
                    'periode_0_30'    => (float) $totalSisaPerPeriode->get('periode_0_30'),
                    'periode_31_60'   => (float) $totalSisaPerPeriode->get('periode_31_60'),
                    'periode_61_90'   => (float) $totalSisaPerPeriode->get('periode_61_90'),
                    'periode_90_up'   => (float) $totalSisaPerPeriode->get('periode_90_up'),
                    'umur_hari'       => '',
                ]])
                ->all(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            "No. Tagihan",
            "No. Rawat",
            "Tgl. Tagihan",
            "Tgl. Jatuh Tempo",
            "Tgl. Bayar",
            "No RM",
            "Pasien",
            "Jaminan Pasien",
            "Jaminan Akun Piutang",
            "Catatan",
            "Status Piutang",
            "Nama Bayar",
            "Piutang",
            "Cicilan",
            "Sisa",
            "0 - 30",
            "31 - 60",
            "61 - 90",
            "> 90",
            "Umur Hari",
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
            'Piutang Aging (Account Receivable)',
            'Per ' . carbon($this->tglAkhir)->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
