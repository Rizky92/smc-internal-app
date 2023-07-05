<?php

namespace App\Http\Livewire\Keuangan\RKAT;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PemantauanRKAT extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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
            ->get()
            ->mapWithKeys(fn (object $row): array => [$row->tahun => $row->tahun])
            ->all();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Bidang>
     */
    public function getDataLaporanRKATProperty(): Collection
    {
        return Bidang::query()
            ->with([
                'anggaranBidang' => fn (HasMany $q) => $q->withSum('detailPemakaian as total_pemakaian', 'nominal'),
                'anggaranBidang.anggaran'
            ])
            ->get();
    }

    public function render(): View
    {
        return view('livewire.keuangan.rkat.pemantauan-rkat')
            ->layout(BaseLayout::class, ['title' => 'Pemantauan Pemakaian RKAT per Bidang']);
    }

    protected function defaultValues(): void
    {
        $this->tahun = now()->format('Y');
    }

    protected function dataPerSheet(): array
    {
        $pemakaianAnggaran = PemakaianAnggaran::query()
            ->selectRaw("anggaran_bidang_id, month(tgl_dipakai) as bulan, sum(nominal_pemakaian) as total_dipakai")
            ->whereRaw('year(tgl_dipakai) = ?', $this->tahun)
            ->groupByRaw("anggaran_bidang_id, month(tgl_dipakai)")
            ->get();

        $anggaranBidang = AnggaranBidang::query()
            ->with(['anggaran', 'bidang'])
            ->whereTahun($this->tahun)
            ->get();

        return [
            $anggaranBidang->map(function (AnggaranBidang $model, int $_) use ($pemakaianAnggaran): array {
                $pemakaianAnggaranBidang = $pemakaianAnggaran
                    ->where('anggaran_bidang_id', $model->getKey())
                    ->mapWithKeys(fn (PemakaianAnggaran $item, int $_): array => [$item->bulan => round(floatval($item->total_dipakai), 2)]);

                $nominal = round($model->nominal_anggaran, 2);

                $total = round(floatval($pemakaianAnggaranBidang->sum()), 2);

                $selisih = $nominal - $total;

                $persentase = $total > 0
                    ? round($total / $nominal, 4)
                    : 0;

                $output = collect([
                    'bidang'   => $model->bidang->nama,
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
            'Pemantauan Pemakaian RKAT Tahun ' . $this->tahun,
            'Per ' . now()->translatedFormat('d F Y'),
        ];
    }
}
