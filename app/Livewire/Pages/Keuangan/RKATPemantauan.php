<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Bidang;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\Descendants;

class RKATPemantauan extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tahun;

    protected function queryString(): array
    {
        return [
            'tahun' => ['except' => now()->format('Y')],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataTahunProperty(): array
    {
        return DB::table('anggaran_bidang')
            ->select('tahun')
            ->groupBy('tahun')
            ->orderBy('tahun', 'asc')
            ->pluck('tahun', 'tahun')
            ->all();
    }

    public function getDataLaporanRKATProperty(): Collection
    {
        return Bidang::query()
            ->with([
                'descendants' => fn (Descendants $q) => $q
                    ->with([
                        'anggaranBidang' => fn (HasMany $q) => $q->withSum('detailPemakaian as total_pemakaian', 'nominal'),
                        'anggaranBidang.anggaran',
                    ]),
            ])
            ->isRoot()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.rkat-pemantauan')
            ->layout(BaseLayout::class, ['title' => 'Pemantauan Pemakaian RKAT per Bidang']);
    }

    protected function defaultValues(): void
    {
        $this->tahun = now()->format('Y');
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        $pemakaianAnggaran = PemakaianAnggaran::query()
            ->selectRaw('anggaran_bidang_id, month(tgl_dipakai) as bulan, sum(pemakaian_anggaran_detail.nominal) as total_dipakai')
            ->join('pemakaian_anggaran_detail', 'pemakaian_anggaran.id', '=', 'pemakaian_anggaran_detail.pemakaian_anggaran_id')
            ->join('anggaran_bidang', 'pemakaian_anggaran.anggaran_bidang_id', '=', 'anggaran_bidang.id')
            ->where('anggaran_bidang.tahun', $this->tahun)
            ->groupByRaw('anggaran_bidang_id, month(tgl_dipakai)')
            ->withCasts(['bulan' => 'int', 'total_dipakai' => 'float'])
            ->get();

        $anggaranBidang = AnggaranBidang::query()
            ->with(['anggaran', 'bidang', 'bidang.parent'])
            ->whereTahun($this->tahun)
            ->get();

        return [
            $anggaranBidang->map(function (AnggaranBidang $model, int $_) use ($pemakaianAnggaran): array {
                $pemakaianAnggaranBidang = $pemakaianAnggaran
                    ->where('anggaran_bidang_id', $model->getKey())
                    ->mapWithKeys(fn (PemakaianAnggaran $item, int $_): array => [$item->bulan => round($item->total_dipakai, 2)]);

                $nominal = round($model->nominal_anggaran, 2);

                $total = round($pemakaianAnggaranBidang->sum(), 2);

                $selisih = $nominal - $total;

                $persentase = $total > 0
                    ? round($total / $nominal, 4)
                    : 0;

                $output = collect([
                    'bidang'   => optional($model->bidang->parent)->nama ?? $model->bidang->nama,
                    'unit'     => $model->bidang->nama,
                    'anggaran' => $model->anggaran->nama,
                    'nominal'  => $nominal,
                ])
                    ->merge(map_bulan($pemakaianAnggaranBidang))
                    ->merge([
                        'total'      => $total,
                        'selisih'    => $selisih,
                        'persentase' => $persentase,
                    ]);

                return $output->all();
            }),
        ];
    }

    protected function columnHeaders(): array
    {
        return collect(carbon('2023-01-01')
            ->setYear(intval($this->tahun))
            ->toPeriod(carbon()->endOfYear(), '1 month'))
            ->map
            ->translatedFormat('F')
            ->prepend('Nominal')
            ->prepend('Anggaran')
            ->prepend('Unit')
            ->prepend('Bidang')
            ->push('Total')
            ->push('Selisih')
            ->push('Persentase')
            ->all();
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Pemantauan Pemakaian RKAT Tahun '.$this->tahun,
            'Per '.now()->translatedFormat('d F Y'),
        ];
    }
}
