<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\PiutangPasien;
use App\Models\RekamMedis\Penjamin;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class RekapPiutangPasien extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $caraBayar;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'   => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'  => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
            'caraBayar' => ['except' => '', 'as' => 'kdpj'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getPenjaminProperty(): array
    {
        return Penjamin::where('status', '1')->pluck('png_jawab', 'kd_pj')->all();
    }

    public function getPiutangPasienProperty()
    {
        return $this->isDeferred ? [] : PiutangPasien::query()
            ->rekapPiutangPasien($this->tglAwal, $this->tglAkhir, $this->caraBayar)
            ->search($this->cari, [
                'piutang_pasien.no_rawat',
                'piutang_pasien.no_rkm_medis',
                'pasien.nm_pasien',
                'piutang_pasien.status',
                'penjab.png_jawab',
            ])
            ->sortWithColumns($this->sortColumns, [
                'total'     => 'piutang_pasien.totalpiutang',
                'uang_muka' => 'piutang_pasien.uangmuka',
                'terbayar'  => DB::raw('ifnull(sisa_piutang.sisa, 0)'),
                'sisa'      => DB::raw('(piutang_pasien.sisapiutang - ifnull(sisa_piutang.sisa, 0))'),
                'penjamin'  => 'penjab.png_jawab',
            ])
            ->paginate($this->perpage);
    }

    public function getTotalTagihanPiutangPasienProperty(): float
    {
        return $this->isDeferred ? [] : PiutangPasien::query()
            ->rekapPiutangPasien($this->tglAwal, $this->tglAkhir, $this->caraBayar)
            ->search($this->cari, [
                'piutang_pasien.no_rawat',
                'piutang_pasien.no_rkm_medis',
                'pasien.nm_pasien',
                'piutang_pasien.status',
                'penjab.png_jawab',
            ])
            ->sum(DB::raw('round(piutang_pasien.sisapiutang - ifnull(sisa_piutang.sisa, 0))'));
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.rekap-piutang-pasien')
            ->layout(BaseLayout::class, ['title' => 'Rekap Data Tagihan Piutang Pasien']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->caraBayar = '';
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        $query = PiutangPasien::query()
            ->rekapPiutangPasien($this->tglAwal, $this->tglAkhir, $this->caraBayar);

        return [
            fn () => $query
                ->orderBy('penjab.png_jawab')
                ->cursor()
                ->map(fn ($model): array => [
                    'no_rawat'     => $model->no_rawat,
                    'no_rkm_medis' => $model->no_rkm_medis,
                    'nm_pasien'    => $model->nm_pasien,
                    'tgl_piutang'  => $model->tgl_piutang,
                    'status'       => $model->status,
                    'total'        => round(floatval($model->total), 2),
                    'uang_muka'    => round(floatval($model->uang_muka), 2),
                    'terbayar'     => round(floatval($model->terbayar), 2),
                    'sisa'         => round(floatval($model->sisa), 2),
                    'tgltempo'     => $model->tgltempo,
                    'penjamin'     => $model->penjamin,
                ])
                ->merge([[
                    'no_rawat'     => 'TOTAL',
                    'no_rkm_medis' => '',
                    'nm_pasien'    => '',
                    'tgl_piutang'  => '',
                    'status'       => '',
                    'total'        => round(floatval($query->sum(DB::raw('round(piutang_pasien.totalpiutang, 2)'))), 2),
                    'uang_muka'    => round(floatval($query->sum(DB::raw('round(piutang_pasien.uangmuka, 2)'))), 2),
                    'terbayar'     => round(floatval($query->sum(DB::raw('round(ifnull(sisa_piutang.sisa, 0), 2)'))), 2),
                    'sisa'         => round(floatval($query->sum(DB::raw('round(piutang_pasien.sisapiutang - ifnull(sisa_piutang.sisa, 0), 2)'))), 2),
                    'tgltempo'     => '',
                    'penjamin'     => '',
                ]]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. RM',
            'Pasien',
            'Tgl. Piutang',
            'Status',
            'Total (RP)',
            'Uang Muka (RP)',
            'Terbayar (RP)',
            'Sisa (RP)',
            'Tgl. Jatuh Tempo',
            'Penjamin',
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
            'Rekap Data Tagihan Piutang Pasien',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
