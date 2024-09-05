<?php

namespace App\Jobs;

use App\Models\Aplikasi\User;
use App\Models\Keuangan\Jurnal\Jurnal;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Notification;
use Rizky92\Xlswriter\ExcelExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportToExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $kodeRekening;

    private string $tglAwal;

    private string $tglAkhir;

    private string $userId;

    private string $cari;

    /**
     * @param array{
     *      kodeRekening: string,
     *      tglAwal: string,
     *      tglAkhir: string,
     *      cari: string,
     *      userId: string,
     * } $params
     */

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $params)
    {
        $this->kodeRekening = $params['kodeRekening'];
        $this->tglAwal = $params['tglAwal'];
        $this->tglAkhir = $params['tglAkhir'];
        $this->cari = $params['cari'];
        $this->userId = $params['userId'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $filePath = $this->beginExcelExport();
        $user = User::findByNRP($this->userId);
        if ($user) {
            // Use the mysql_smc connection explicitly
            DB::connection('mysql_smc')->transaction(function () use ($user, $filePath) {
                Notification::send($user, new ExportReadyNotification($user, $filePath));
            });
        } else {
            \Log::error("User with ID {$this->userId} not found.");
        }
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
        $rekening = $this->kodeRekening ? $this->kodeRekening : 'Semua Rekening';

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

    public function beginExcelExport(): ?StreamedResponse
    {
        $filename = now()->format('Ymd_His').'_';

        $filename .= method_exists($this, 'filename')
            ? str($this->filename())->trim()->snake()->value()
            : str()->snake(class_basename($this));

        $filename .= '.xlsx';

        $dataSheets = $this->dataPerSheet();

        $firstSheet = array_keys($dataSheets)[0] ?: 'Sheet 1';

        $firstData = Arr::isList($dataSheets)
            ? $dataSheets[0]
            : $dataSheets[$firstSheet];

        $firstData = is_callable($firstData) ? $firstData() : $firstData;

        File::ensureDirectoryExists(storage_path('app/public/excel'));

        $excel = ExcelExport::make($filename, $firstSheet)
            ->setPageHeaders($this->pageHeaders())
            ->setColumnHeaders($this->columnHeaders())
            ->setData($firstData);

        array_shift($dataSheets);

        foreach ($dataSheets as $sheet => $data) {
            if (is_callable($data)) {
                $excel->addSheet($sheet)->setData($data());
            } else {
                $excel->addSheet($sheet)->setData($data);
            }
        }

        return $excel->export();
    }
}
