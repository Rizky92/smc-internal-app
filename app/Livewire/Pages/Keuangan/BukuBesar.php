<?php

namespace App\Livewire\Pages\Keuangan;

use App\EloquentSerialize\Facades\EloquentSerializeFacade;
use App\Jobs\ExportToExcel;
use App\Jobs\Exporter\Keuangan\BukuBesarExporter;
use App\Jobs\Keuangan\ExportQuery;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Rekening;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Throwable;

class BukuBesar extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

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

    public function getBukuBesarProperty()
    {
        return $this->isDeferred ? [] : Jurnal::query()
            ->bukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
            ->with(['pengeluaranHarian', 'piutangDilunaskan.tagihan'])
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns, [
                'tgl_jurnal' => 'asc',
                'jam_jurnal' => 'asc',
            ])
            ->paginate($this->perpage);
    }

    public function getTotalDebetDanKreditProperty()
    {
        return $this->isDeferred ? [] : Jurnal::query()
            ->jumlahDebetKreditBukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
            ->search($this->cari)
            ->first();
    }

    public function getRekeningProperty(): array
    {
        return Cache::remember('rekening_bukubesar', now()->addDay(), fn () => Rekening::query()
            ->orderBy('kd_rek')
            ->pluck('nm_rek', 'kd_rek')
            ->all());
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.buku-besar')
            ->layout(BaseLayout::class, ['title' => 'Jurnal Buku Besar']);
    }

    protected function dataPerSheet(): array
    {
        return [
            Jurnal::query()
                ->bukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
                ->with(['pengeluaranHarian', 'piutangDilunaskan'])
                ->search($this->cari)
                ->cursor()
                ->map(fn (Jurnal $model): array => [
                    'tgl_jurnal'             => $model->tgl_jurnal,
                    'jam_jurnal'             => $model->jam_jurnal,
                    'no_jurnal'              => $model->no_jurnal,
                    'no_bukti'               => $model->no_bukti,
                    'keterangan'             => $model->keterangan,
                    'keterangan_pengeluaran' => optional($model->pengeluaranHarian)->keterangan ?? '-',
                    'catatan'                => $this->getCatatanPiutang($model),
                    'kd_rek'                 => $model->kd_rek,
                    'nm_rek'                 => $model->nm_rek,
                    'debet'                  => round($model->debet, 2),
                    'kredit'                 => round($model->kredit, 2),
                ])
                ->merge([[
                    'tgl_jurnal'             => '',
                    'jam_jurnal'             => '',
                    'no_jurnal'              => '',
                    'no_bukti'               => '',
                    'keterangan'             => '',
                    'keterangan_pengeluaran' => '',
                    'catatan'                => '',
                    'kd_rek'                 => '',
                    'nm_rek'                 => 'TOTAL :',
                    'debet'                  => round(optional($this->totalDebetDanKredit)->debet, 2),
                    'kredit'                 => round(optional($this->totalDebetDanKredit)->kredit, 2),
                ]])
                ->all(),
        ];
    }

    protected function getCatatanPiutang(Jurnal $model): string
    {
        return optional(optional($model->piutangDilunaskan)->tagihan)->catatan ?? '-';
    }

    protected function columnHeaders(): array
    {
        return [
            'Tgl',
            'Jam',
            'No. Jurnal',
            'No. Bukti',
            'Keterangan Jurnal',
            'Keterangan Pengeluaran',
            'Catatan Penagihan',
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

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Buku Besar rekening '.$rekening,
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }

    protected function defaultValues(): void
    {
        $this->kodeRekening = '';
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    public function exportToExcel()
    { 
        // $batchId = (string) Str::uuid();

        // $filters = [
        //     'kodeRekening' => $this->kodeRekening,
        //     'tglAwal'      => $this->tglAwal,
        //     'tglAkhir'     => $this->tglAkhir,
        //     'cari'         => $this->cari,
        // ];

        // $userId = user()->nik;

        // Bus::chain([
        //     BukuBesarExporter::dispatch($batchId, $filters, $userId),
        //     ExportToExcel::dispatch($batchId, $userId),
        // ])->catch(function (Throwable $e) {
        //     $e->getMessage();
        // });

        // $this->emit('flash.info', 'Proses export ke Excel telah dimulai, silahkan tunggu beberapa saat.');

        $builder = Jurnal::query()
            ->bukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
            ->with(['pengeluaranHarian', 'piutangDilunaskan'])
            ->search($this->cari);

        $query = EloquentSerializeFacade::serialize($builder);

        $columnHeaders = $this->columnHeaders();

        $userId = user()->nik;

        $mapper = (fn (Jurnal $model): array => [
            'tgl_jurnal'             => $model->tgl_jurnal,
            'jam_jurnal'             => $model->jam_jurnal,
            'no_jurnal'              => $model->no_jurnal,
            'no_bukti'               => $model->no_bukti,
            'keterangan'             => $model->keterangan,
            'keterangan_pengeluaran' => optional($model->pengeluaranHarian)->keterangan ?? '-',
            'catatan'                => $this->getCatatanPiutang($model),
            'kd_rek'                 => $model->kd_rek,
            'nm_rek'                 => $model->nm_rek,
            'debet'                  => round($model->debet, 2),
            'kredit'                 => round($model->kredit, 2),
        ]);

        ExportQuery::dispatch($query, $columnHeaders, $mapper, $userId);

        $this->emit('flash.info', 'Proses export ke Excel telah dimulai, silahkan tunggu beberapa saat.');
    }
}