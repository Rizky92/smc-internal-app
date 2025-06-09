<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\Rekening;
use App\Models\RekamMedis\Penjamin;
use App\View\Components\BaseLayout;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\View\View;
use Livewire\Component;

class LabaRugiRekeningPerPeriode extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $kodePenjamin;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'kodePenjamin' => ['except' => '', 'as' => 'penjamin'],
            'tglAwal'      => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'     => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getLabaRugiPerRekeningProperty(): Collection
    {
        if ($this->isDeferred) {
            return collect(['D' => [], 'K' => []]);
        }

        $semuaRekening = Rekening::semuaRekening()
            ->whereTipe('R')
            ->get();

        $debetKredit = Rekening::query()
            ->hitungDebetKreditPerPeriode($this->tglAwal, $this->tglAkhir, $this->kodePenjamin)
            ->get();

        return $semuaRekening
            ->merge($debetKredit)
            ->map(function (Rekening $rekening): Fluent {
                $total = 0;

                $debet = $rekening->debet ?? 0;
                $kredit = $rekening->kredit ?? 0;

                if ($rekening->balance === 'K') {
                    $total = $kredit - $debet;
                }

                if ($rekening->balance === 'D') {
                    $total = $debet - $kredit;
                }

                return new Fluent(array_merge(
                    $rekening->only('kd_rek', 'nm_rek', 'balance'),
                    [
                        'debet'  => floatval($debet),
                        'kredit' => floatval($kredit),
                        'total'  => floatval($total),
                    ],
                ));
            })
            ->mapToGroups(fn ($item): array => [$item->balance => $item]);
    }

    public function getTotalLabaRugiPerRekeningProperty(): array
    {
        $pendapatan = collect($this->labaRugiPerRekening->get('K'));

        $bebanDanBiaya = collect($this->labaRugiPerRekening->get('D'));

        $totalDebetPendapatan = $pendapatan->sum('debet');
        $totalKreditPendapatan = $pendapatan->sum('kredit');
        $totalPendapatan = $totalKreditPendapatan - $totalDebetPendapatan;

        $totalDebetBeban = $bebanDanBiaya->sum('debet');
        $totalKreditBeban = $bebanDanBiaya->sum('kredit');
        $totalBebanDanBiaya = $totalDebetBeban - $totalKreditBeban;

        $labaRugi = $totalPendapatan - $totalBebanDanBiaya;

        return compact(
            'totalPendapatan',
            'totalDebetPendapatan',
            'totalKreditPendapatan',
            'totalBebanDanBiaya',
            'totalDebetBeban',
            'totalKreditBeban',
            'labaRugi'
        );
    }

    public function getPenjaminProperty(): array
    {
        return Penjamin::where('status', '=', '1')->pluck('png_jawab', 'kd_pj')->all();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laba-rugi-rekening-per-periode')
            ->layout(BaseLayout::class, ['title' => 'Laporan Laba Rugi']);
    }

    protected function defaultValues(): void
    {
        $this->kodePenjamin = '';
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
    }

    protected function mapDataForExcelExport(): Collection
    {
        $pendapatanRowHeader = $this->insertExcelRow('', 'PENDAPATAN');
        $bebanRowHeader = $this->insertExcelRow('', 'BEBAN & BIAYA');
        $empty = $this->insertExcelRow();

        $pendapatan = $this->labaRugiPerRekening->get('K');
        $beban = $this->labaRugiPerRekening->get('D');

        $total = $this->totalLabaRugiPerRekening;

        $totalPendapatanRow = $this->insertExcelRow('', 'TOTAL', '', $total['totalDebetPendapatan'], $total['totalKreditPendapatan'], $total['totalPendapatan']);
        $totalBebanRow = $this->insertExcelRow('', 'TOTAL', '', $total['totalDebetBeban'], $total['totalKreditBeban'], $total['totalBebanDanBiaya']);

        $pendapatanBersih = $this->insertExcelRow('', 'PENDAPATAN BERSIH', '', $total['totalPendapatan'], $total['totalBebanDanBiaya'], $total['labaRugi']);

        return collect([$pendapatanRowHeader])
            ->merge($pendapatan)
            ->merge([$totalPendapatanRow, $empty])
            ->merge([$bebanRowHeader])
            ->merge($beban)
            ->merge([$totalBebanRow, $empty])
            ->merge([$pendapatanBersih]);
    }

    private function insertExcelRow(string $kd_rek = '', string $nm_rek = '', string $balance = '', string $debet = '', string $kredit = '', string $total = ''): Fluent
    {
        return new Fluent(func_get_named_args($this, 'insertExcelRow', func_get_args()));
    }

    /**
     * @return Collection[]
     *
     * @psalm-return array{0: Collection}
     */
    protected function dataPerSheet(): array
    {
        return [
            $this->mapDataForExcelExport(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Akun',
            'Nama Akun',
            'Jenis',
            'Debet',
            'Kredit',
            'Total',
        ];
    }

    protected function pageHeaders(): array
    {
        $penjamin = empty($this->kodePenjamin) ? 'SEMUA' : $this->penjamin[$this->kodePenjamin];

        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Laporan Laba Rugi Keuangan penjamin '.$penjamin,
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
