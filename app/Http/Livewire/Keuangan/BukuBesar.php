<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\PengeluaranHarian;
use App\Models\Keuangan\Rekening;
use App\Support\Livewire\Concerns\DeferredLoading;
use App\Support\Livewire\Concerns\ExcelExportable;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\LiveTable;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class BukuBesar extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $kodeRekening;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'kodeRekening' => ['except' => '', 'as' => 'rekening'],
            'tglAwal'      => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir'     => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\Paginator|array<empty, empty>
     */
    public function getBukuBesarProperty()
    {
        return $this->isDeferred
            ? []
            : Jurnal::query()
            ->bukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
            ->with('pengeluaranHarian')
            ->search($this->cari, [
                'jurnal.tgl_jurnal',
                'jurnal.jam_jurnal',
                'jurnal.no_jurnal',
                'jurnal.no_bukti',
                'jurnal.keterangan',
                'detailjurnal.kd_rek',
                'rekening.nm_rek',
                'detailjurnal.debet',
                'detailjurnal.kredit',
            ])
            ->sortWithColumns($this->sortColumns, [], [
                'tgl_jurnal' => 'asc',
                'jam_jurnal' => 'asc',
            ])
            ->paginate($this->perpage);
    }

    /**
     * @return \App\Models\Keuangan\Jurnal\Jurnal|array<empty, empty>
     */
    public function getTotalDebetDanKreditProperty()
    {
        return $this->isDeferred
            ? []
            : Jurnal::query()
            ->jumlahDebetDanKreditBukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
            ->first();
    }

    public function getRekeningProperty(): array
    {
        return Rekening::query()
            ->orderBy('kd_rek')
            ->pluck('nm_rek', 'kd_rek')
            ->all();
    }

    public function render(): View
    {
        return view('livewire.keuangan.buku-besar')
            ->layout(BaseLayout::class, ['title' => 'Jurnal Buku Besar']);
    }

    protected function defaultValues(): void
    {
        $this->kodeRekening = '';
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            Jurnal::query()
                ->bukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
                ->with('pengeluaranHarian')
                ->cursor()
                ->map(fn (Jurnal $model): array => [
                    'tgl_jurnal' => $model->tgl_jurnal,
                    'jam_jurnal' => $model->jam_jurnal,
                    'no_jurnal'  => $model->no_jurnal,
                    'no_bukti'   => $model->no_bukti,
                    'keterangan' => $model->keterangan,
                    'keterangan_harian' => optional($model->pengeluaranHarian)->keterangan,
                    'kd_rek'     => $model->kd_rek,
                    'nm_rek'     => $model->nm_rek,
                    'debet'      => round($model->debet, 2),
                    'kredit'     => round($model->kredit, 2),
                ])
                ->merge([
                    [
                        'tgl_jurnal' => '',
                        'jam_jurnal' => '',
                        'no_jurnal'  => '',
                        'no_bukti'   => '',
                        'keterangan' => '',
                        'keterangan_harian' => '',
                        'kd_rek'     => '',
                        'nm_rek'     => 'TOTAL :',
                        'debet'      => round(optional($this->totalDebetDanKredit)->debet, 2),
                        'kredit'     => round(optional($this->totalDebetDanKredit)->kredit, 2),
                    ]
                ])
                ->all(),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tgl.',
            'Jam',
            'No. Jurnal',
            'No. Bukti',
            'Keterangan Jurnal',
            'Keterangan Pengeluaran',
            'Kode',
            'Rekening',
            'Debet',
            'Kredit',
        ];
    }

    protected function pageHeaders(): array
    {
        $rekening = empty($this->kodeRekening) ? 'SEMUA' : $this->rekening[$this->kodeRekening];

        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Buku Besar rekening ' . $rekening,
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
