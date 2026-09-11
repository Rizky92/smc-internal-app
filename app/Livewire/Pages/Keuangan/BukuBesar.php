<?php

namespace App\Livewire\Pages\Keuangan;

use App\Jobs\PrepareExport;
use App\Jobs\WriteExcel;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\ExportSession;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Rekening;
use App\Notifications\Notification;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

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

    public int $option;

    /** @var int */
    private const EXCEL_EXPORT = 1;

    /** @var int */
    private const BACKGROUND_EXPORT = 2;

    protected function queryString(): array
    {
        return [
            'kodeRekening' => ['except' => '', 'as' => 'rekening'],
            'tglAwal'      => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'     => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
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
            ->sortWithColumns($this->sortColumns, [
                'tgl_jurnal' => 'asc',
                'jam_jurnal' => 'asc',
            ])
            ->search($this->cari)
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

    public function exportWithOption(int $option): void
    {
        $this->option = $option;

        if ($this->option === self::EXCEL_EXPORT) {
            $this->exportToExcel();
        } elseif ($this->option === self::BACKGROUND_EXPORT) {
            $this->exportToBackground();
        }
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => Jurnal::query()
                ->bukuBesar($this->tglAwal, $this->tglAkhir, $this->kodeRekening)
                ->search($this->cari)
                ->cursor()
                ->map(fn (Jurnal $model): array => [
                    'tgl_jurnal'             => $model->tgl_jurnal,
                    'jam_jurnal'             => $model->jam_jurnal,
                    'no_jurnal'              => $model->no_jurnal,
                    'no_bukti'               => $model->no_bukti,
                    'keterangan'             => $model->keterangan,
                    'keterangan_pengeluaran' => optional($model->pengeluaranHarian)->keterangan ?? '-',
                    'catatan_piutang'        => $this->getCatatanPiutang($model),
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
                    'catatan_piutang'        => '',
                    'kd_rek'                 => '',
                    'nm_rek'                 => 'TOTAL :',
                    'debet'                  => round(optional($this->totalDebetDanKredit)->debet, 2),
                    'kredit'                 => round(optional($this->totalDebetDanKredit)->kredit, 2),
                ]]),
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
            'Catatan Piutang',
            'Kode',
            'Rekening',
            'Debet',
            'Kredit',
        ];
    }

    protected function backgroundExportColumnHeaders(): array
    {
        return [
            'Tgl',
            'Jam',
            'No. Jurnal',
            'No. Bukti',
            'Keterangan Jurnal',
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
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
    }

    public function exportToBackground(): void
    {
        $userId = user()->nik;

        $exportSessionId = Str::uuid()->toString();

        $exportName = 'buku-besar';

        $chunkSize = 1000;

        $existingSession = ExportSession::query()
            ->where('id_user', $userId)
            ->where('export_name', $exportName)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if ($existingSession) {
            $this->emit('flash.error', 'Anda sudah memiliki proses export Buku Besar yang sedang berjalan. Silahkan tunggu hingga proses tersebut selesai sebelum memulai export baru.');

            return;
        }

        ExportSession::query()->create([
            'session_id'  => $exportSessionId,
            'id_user'     => $userId,
            'export_name' => $exportName,
            'status'      => 'pending',
        ]);

        Bus::batch([
            new PrepareExport([
                'exportSessionId' => $exportSessionId,
                'exportName'      => $exportName,
                'userId'          => $userId,
                'tglAwal'         => $this->tglAwal,
                'tglAkhir'        => $this->tglAkhir,
                'kodeRekening'    => $this->kodeRekening,
                'columnHeaders'   => $this->backgroundExportColumnHeaders(),
                'chunkSize'       => $chunkSize,
            ]),
        ])
            ->then(function () use ($exportSessionId, $exportName, $userId) {
                WriteExcel::dispatch([
                    'exportSessionId' => $exportSessionId,
                    'exportName'      => $exportName,
                    'userId'          => $userId,
                ])->onQueue('exports');
            })
            ->allowFailures()
            ->onQueue('exports')
            ->dispatch();

        Notification::make()
            ->message('Export Buku Besar sedang berjalan')
            ->info()
            ->send(user());

        $this->dispatch('flash.info', 'Proses export ke Excel telah dimulai, silahkan tunggu beberapa saat.');
    }
}
