<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\Jurnal\Jurnal;
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
            return collect(['D' => collect(), 'K' => collect()]);
        }

        $groups = Jurnal::query()->labaRugiRalan($this->tglAwal, $this->tglAkhir, $this->kodePenjamin)
            ->unionAll(Jurnal::query()->labaRugiRanap($this->tglAwal, $this->tglAkhir, $this->kodePenjamin))
            ->unionAll(Jurnal::query()->labaRugi($this->tglAwal, $this->tglAkhir, $this->kodePenjamin))
            ->get()
            ->map(fn ($item): Fluent => new Fluent([
                'unit'         => $item->unit,
                'nm_dokter'    => $item->nm_dokter ?: '-',
                'kd_rek'       => $item->kd_rek,
                'nm_rek'       => $item->nm_rek,
                'balance'      => $item->balance,
                'debet'        => floatval($item->debet),
                'kredit'       => floatval($item->kredit),
                'total'        => $item->balance === 'K'
                    ? floatval($item->kredit - $item->debet)
                    : floatval($item->debet - $item->kredit),
            ]))
            ->mapToGroups(fn ($item): array => [$item->balance => $item]);

        return collect(['D' => collect(), 'K' => collect()])->merge($groups);
    }

    public function getTotalLabaRugiPerRekeningProperty(): array
    {
        $semua = $this->labaRugiPerRekening;

        $pendapatan = $semua->get('K', collect());

        $bebanDanBiaya = $semua->get('D', collect());

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

        $pendapatan = $this->labaRugiPerRekening->get('K', collect());
        $beban = $this->labaRugiPerRekening->get('D', collect());

        $total = $this->totalLabaRugiPerRekening;

        $totalPendapatanRow = $this->insertExcelRow('', 'TOTAL', '', '', '', $total['totalDebetPendapatan'], $total['totalKreditPendapatan'], $total['totalPendapatan']);
        $totalBebanRow = $this->insertExcelRow('', 'TOTAL', '', '', '', $total['totalDebetBeban'], $total['totalKreditBeban'], $total['totalBebanDanBiaya']);

        $pendapatanBersih = $this->insertExcelRow('', 'PENDAPATAN BERSIH', '', '', '', $total['totalPendapatan'], $total['totalBebanDanBiaya'], $total['labaRugi']);

        return collect([$pendapatanRowHeader])
            ->merge($pendapatan)
            ->merge([$totalPendapatanRow, $empty])
            ->merge([$bebanRowHeader])
            ->merge($beban)
            ->merge([$totalBebanRow, $empty])
            ->merge([$pendapatanBersih]);
    }

    private function insertExcelRow(string $unit = '', string $nm_dokter = '', string $kd_rek = '', string $nm_rek = '', string $balance = '', string $debet = '', string $kredit = '', string $total = ''): Fluent
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
            'Unit',
            'Dokter',
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
