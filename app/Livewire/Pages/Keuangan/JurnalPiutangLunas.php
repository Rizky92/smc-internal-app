<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\BayarPiutang;
use App\Models\Keuangan\PiutangDilunaskan;
use App\View\Components\BaseLayout;
use Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class JurnalPiutangLunas extends Component
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
    public $kodeRekening;

    /** @var string */
    public $jenisPeriode;

    protected function queryString(): array
    {
        return [
            'tglAwal'      => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'     => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'kodeRekening' => ['except' => '-', 'as' => 'rekening'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.jurnal-piutang-lunas')
            ->layout(BaseLayout::class, ['title' => 'Penarikan Data Penagihan Piutang Dibayar dari Jurnal']);
    }

    public function getAkunPenagihanPiutangProperty()
    {
        return Cache::remember('akun_piutang_lunas', now()->addDay(), fn () => DB::connection('mysql_sik')
            ->table('rekening')
            ->whereIn('kd_rek', BayarPiutang::select('kd_rek'))
            ->pluck('nm_rek', 'kd_rek'));
    }

    public function tarikDataTerbaru(): void
    {
        PiutangDilunaskan::refreshModel();

        $this->fullRefresh();

        $this->flashSuccess('Data Berhasil Diperbaharui!');
    }

    public function getDataPiutangDilunaskanProperty()
    {
        return $this->isDeferred ? [] : PiutangDilunaskan::query()
            ->dataPiutangDilunaskan($this->tglAwal, $this->tglAkhir, $this->kodeRekening, $this->jenisPeriode)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    protected function defaultValues(): void
    {
        $this->kodeRekening = '-';
        $this->jenisPeriode = 'jurnal';
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => PiutangDilunaskan::query()
                ->dataPiutangDilunaskan($this->tglAwal, $this->tglAkhir, $this->kodeRekening, $this->jenisPeriode)
                ->cursor()
                ->map(fn (PiutangDilunaskan $model): array => [
                    'no_jurnal'       => $model->no_jurnal,
                    'waktu_jurnal'    => carbon($model->waktu_jurnal)->toDateString(),
                    'no_rawat'        => $model->no_rawat,
                    'no_rkm_medis'    => $model->no_rkm_medis.' '.$model->nm_pasien.' '."({$model->umur})",
                    'nama_penjamin'   => $model->nama_penjamin,
                    'no_tagihan'      => $model->no_tagihan,
                    'nik_penagih'     => $model->nik_penagih.' '.$model->nama_penagih,
                    'nik_penyetuju'   => $model->nik_penyetuju.' '.$model->nama_penyetuju,
                    'piutang_dibayar' => floatval($model->piutang_dibayar),
                    'tgl_penagihan'   => carbon($model->tgl_penagihan)->toDateString(),
                    'tgl_jatuh_tempo' => carbon($model->tgl_jatuh_tempo)->toDateString(),
                    'tgl_bayar'       => carbon($model->tgl_bayar)->toDateString(),
                    'status'          => $model->status,
                    'nik_validasi'    => $model->nik_validasi.' '.$model->nama_pemvalidasi,
                    'kd_rek'          => $model->kd_rek.' '.$model->nm_rek,
                    'keterangan'      => $model->keterangan,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Jurnal',
            'Tgl. Jurnal',
            'No. Rawat',
            'Pasien',
            'Penjamin',
            'No. Tagihan',
            'Penagih',
            'Verifikasi',
            'Nominal',
            'Tgl. Tagihan',
            'Tgl. Jatuh Tempo',
            'Tgl. Dibayar',
            'Status',
            'Validasi oleh',
            'Rekening',
            'Keterangan',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Penarikan Data Penagihan Piutang Dibayar dari Jurnal',
            now()->translatedFormat('d F Y'),
            'Berdasarkan Tgl. '.Str::title($this->jenisPeriode).', '.$periode,
        ];
    }
}
