<?php

namespace App\Livewire\Pages\Marketing;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\TindakanRalanDokter;
use App\Models\Perawatan\TindakanRalanDokterPerawat;
use App\Models\Perawatan\TindakanRalanPerawat;
use App\Models\Perawatan\TindakanRanapDokter;
use App\Models\Perawatan\TindakanRanapDokterPerawat;
use App\Models\Perawatan\TindakanRanapPerawat;
use App\Models\Radiologi\PeriksaRadiologi;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\LazyCollection;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPenggunaanAlkes extends Component
{
    use FlashComponent;
    use Filterable;
    use ExcelExportable;
    use LiveTable;
    use MenuTracker;
    use DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    private const ALKES_EXCLUDE = [
        'lumbal' => ['mri', 'ct'],
        'thorax' => ['ct'],
    ];

    private const MCU_KD_POLI = 'U0036';

    private const ALKES_SUMMARY_TINDAKAN = [
        'Audiometri' => 'audiometri',
        'Spirometri' => 'spirometri',
        'Treadmil'   => 'treadmill',
        'EKG'        => 'ekg',
        'EEG'        => 'eeg',
        'Echo'       => 'echo',
    ];

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesAudiometriProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'audiometri');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesSpirometriProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'spirometri');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesTreadmillProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'treadmill');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesEKGProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ekg');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesEEGProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'eeg');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    /**
     * @return array|\Illuminate\Pagination\Paginator
     */
    public function getDataPenggunaanAlkesEchoProperty()
    {
        $ralanDr = TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');
        $ralanDrPr = TindakanRalanDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');
        $ralanPr = TindakanRalanPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');

        $ranapDr = TindakanRanapDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');
        $ranapDrPr = TindakanRanapDokterPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');
        $ranapPr = TindakanRanapPerawat::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'echo');

        return $this->isDeferred ? [] : $ralanDr
            ->unionAll($ralanDrPr)
            ->unionAll($ralanPr)
            ->unionAll($ranapDr)
            ->unionAll($ranapDrPr)
            ->unionAll($ranapPr)
            ->paginate($this->perpage);
    }

    public function getDataPenggunaanAlkesUSGProperty()
    {
        return $this->isDeferred ? [] : PeriksaRadiologi::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, ['usg', 'hsg'])
            ->paginate($this->perpage);
    }

    public function getDataPenggunaanAlkesThoraxProperty()
    {
        return $this->isDeferred ? [] : PeriksaRadiologi::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'thorax', self::ALKES_EXCLUDE['thorax'] ?? [])
            ->paginate($this->perpage);
    }

    public function getDataPenggunaanAlkesCTScanProperty()
    {
        return $this->isDeferred ? [] : PeriksaRadiologi::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ct-scan')
            ->paginate($this->perpage);
    }

    public function getDataPenggunaanAlkesLumbalProperty()
    {
        return $this->isDeferred ? [] : PeriksaRadiologi::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'lumbal', self::ALKES_EXCLUDE['lumbal'] ?? [])
            ->paginate($this->perpage);
    }

    public function getDataPenggunaanAlkesPanoramikProperty()
    {
        return $this->isDeferred ? [] : PeriksaRadiologi::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'panoramik')
            ->paginate($this->perpage);
    }

    public function getDataPenggunaanAlkesMRIProperty()
    {
        return $this->isDeferred ? [] : PeriksaRadiologi::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'mri')
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.marketing.laporan-penggunaan-alkes')
            ->layout(BaseLayout::class, ['title' => 'Laporan Penggunaan Alkes']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    private function buildRalanRanapQuery(string $nama): Builder
    {
        return TindakanRalanDokter::query()
            ->penggunaanAlkes($this->tglAwal, $this->tglAkhir, $nama)
            ->unionAll(TindakanRalanDokterPerawat::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, $nama))
            ->unionAll(TindakanRalanPerawat::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, $nama))
            ->unionAll(TindakanRanapDokter::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, $nama))
            ->unionAll(TindakanRanapDokterPerawat::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, $nama))
            ->unionAll(TindakanRanapPerawat::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, $nama));
    }

    private function mapAlkesItem(object $item): array
    {
        return [
            $item->no_rawat,
            $item->no_rkm_medis,
            $item->nm_pasien,
            $item->nm_perawatan,
            $item->nama_nakes,
            $item->tgl_periksa.' '.$item->jam,
            $item->unit,
            $item->biaya,
            $item->status,
        ];
    }

    private function classifyAlkesUnit(object $item): string
    {
        if ($item->status === 'Ranap') {
            return 'Ranap';
        }

        if ($item->kd_poli === self::MCU_KD_POLI) {
            return 'MCU';
        }

        return 'Poli';
    }

    /**
     * @return array{Poli: int, MCU: int, Ranap: int}
     */
    private function countAlkesByUnit(Builder $query): array
    {
        $counts = ['Poli' => 0, 'MCU' => 0, 'Ranap' => 0];

        foreach ($query->cursor() as $item) {
            $counts[$this->classifyAlkesUnit($item)]++;
        }

        return $counts;
    }

    private function buildSummaryRows(): array
    {
        $resolvers = [
            ...array_map(
                fn (string $nama) => fn (): array => $this->countAlkesByUnit($this->buildRalanRanapQuery($nama)),
                self::ALKES_SUMMARY_TINDAKAN
            ),
            'USG'       => fn (): array => $this->countAlkesByUnit(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, ['usg', 'hsg'])),
            'Thorax'    => fn (): array => $this->countAlkesByUnit(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'thorax', self::ALKES_EXCLUDE['thorax'] ?? [])),
            'CT-Scan'   => fn (): array => $this->countAlkesByUnit(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ct-scan')),
            'Lumbal'    => fn (): array => $this->countAlkesByUnit(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'lumbal', self::ALKES_EXCLUDE['lumbal'] ?? [])),
            'Panoramik' => fn (): array => $this->countAlkesByUnit(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'panoramik')),
            'MRI'       => fn (): array => $this->countAlkesByUnit(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'mri')),
        ];

        $rows = [];
        $no = 1;

        foreach ($resolvers as $tindakan => $resolver) {
            $counts = $resolver();
            $total = $counts['Poli'] + $counts['MCU'] + $counts['Ranap'];

            $rows[] = [$no++, $tindakan, $counts['Poli'], $counts['MCU'], $counts['Ranap'], $total];
        }

        return $rows;
    }

    protected function dataPerSheet(): array
    {
        return [
            'Summary'    => fn () => $this->buildSummaryRows(),
            'Audiometri' => fn () => (new LazyCollection($this->buildRalanRanapQuery('audiometri')->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'Spirometri' => fn () => (new LazyCollection($this->buildRalanRanapQuery('spirometri')->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'Treadmill'  => fn () => (new LazyCollection($this->buildRalanRanapQuery('treadmill')->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'EKG'        => fn () => (new LazyCollection($this->buildRalanRanapQuery('ekg')->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'EEG'        => fn () => (new LazyCollection($this->buildRalanRanapQuery('eeg')->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'Echo'       => fn () => (new LazyCollection($this->buildRalanRanapQuery('echo')->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'USG'        => fn () => (new LazyCollection(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, ['usg', 'hsg'])->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'Thorax'     => fn () => (new LazyCollection(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'thorax', self::ALKES_EXCLUDE['thorax'] ?? [])->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'CT Scan'    => fn () => (new LazyCollection(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'ct-scan')->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'Lumbal'     => fn () => (new LazyCollection(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'lumbal', self::ALKES_EXCLUDE['lumbal'] ?? [])->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'Panoramik'  => fn () => (new LazyCollection(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'panoramik')->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
            'MRI'        => fn () => (new LazyCollection(PeriksaRadiologi::query()->penggunaanAlkes($this->tglAwal, $this->tglAkhir, 'mri')->cursor()))->map(fn ($item) => $this->mapAlkesItem($item)),
        ];
    }

    protected function columnHeaders(): array
    {
        $detailHeaders = [
            'No. Rawat',
            'No. RM',
            'Pasien',
            'Tindakan',
            'Nakes',
            'Tgl Periksa',
            'Unit / Kamar',
            'Biaya',
            'Status',
        ];

        return [
            'Summary'    => ['No.', 'Tindakan', 'Poli', 'MCU', 'Ranap', 'Total'],
            'Audiometri' => $detailHeaders,
            'Spirometri' => $detailHeaders,
            'Treadmill'  => $detailHeaders,
            'EKG'        => $detailHeaders,
            'EEG'        => $detailHeaders,
            'Echo'       => $detailHeaders,
            'USG'        => $detailHeaders,
            'Thorax'     => $detailHeaders,
            'CT Scan'    => $detailHeaders,
            'Lumbal'     => $detailHeaders,
            'Panoramik'  => $detailHeaders,
            'MRI'        => $detailHeaders,
        ];
    }

    protected function pageHeaders(): array
    {
        $periode = 'Periode: '.$this->tglAwal.' s/d '.$this->tglAkhir;

        return [
            'RS Samarinda Medika Citra',
            'Laporan Penggunaan Alkes',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
