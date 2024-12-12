<?php

namespace App\Jobs;

use App\Models\Aplikasi\User;
use App\Models\Keuangan\Rekening;
use App\Models\TemporaryExport;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Vtiful\Kernel\Excel;

class ExportToExcel implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $batchId;
    
    protected $filters;

    protected $userId;

    protected $rekening;

    protected $disk = 'public';

    protected $basePath = 'excel';

    protected $filename;

    protected $chunkSize = 1000;

    protected $maxRowsPerSheet = 1000000;

    /**
     * Create a new job instance.
     * @param string $userId
     */
    public function __construct($batchId, string $userId)
    {
        $this->batchId = $batchId;
        $this->userId = $userId;
    }

    public function rekening()
    {
        $this->rekening =  Rekening::query()
            ->orderBy('kd_rek')
            ->pluck('nm_rek', 'kd_rek')
            ->all();
    }

    public function handle()
    {
        $filename = now()->format('Ymd_His').'_';

        $filename .= method_exists($this, 'filename')
        ? str($this->filename)->trim()->snake()->value()
        : str()->snake(class_basename($this));

        $filename .= '.xlsx';

        $config = [
            'path' => Storage::disk($this->disk)->path($this->basePath)
        ];

        $sheetIndex = 1;
        $excel = (new Excel($config))
            ->fileName($filename, "Sheet $sheetIndex");

        $sheet = $excel->header([
            'Tanggal Jurnal', 
            'Jam Jurnal', 
            'No Jurnal', 
            'No Bukti', 
            'Keterangan', 
            'Keterangan Pengeluaran', 
            'Catatan', 
            'Kode Rek', 
            'Nama Rek', 
            'Debet', 
            'Kredit'
        ]);

        $currentRowCount = 0;

        TemporaryExport::where('batch_id', $this->batchId)
            ->chunk($this->chunkSize, function ($dataChunk) use (&$sheet, &$currentRowCount, &$sheetIndex) {
                // Convert data to array format for xlswriter
                $rows = $dataChunk->map(function ($item) {
                    return [
                        $item->column1,
                        $item->column2,
                        $item->column3,
                        $item->column4,
                        $item->column5,
                        $item->column6,
                        $item->column7,
                        $item->column8,
                        $item->column9,
                        $item->column10,
                        $item->column11,
                    ];
                })->toArray();

                // Check if adding these rows will exceed the max rows per sheet
                if ($currentRowCount + count($rows) > $this->maxRowsPerSheet) {
                    // Calculate how many rows can be added to the current sheet
                    $remainingRows = $this->maxRowsPerSheet - $currentRowCount;

                    // Add the remaining rows to the current sheet
                    $sheet->data(array_slice($rows, 0, $remainingRows));

                    // Create a new sheet
                    $sheetIndex++;
                    // Add the remaining rows to the new sheet
                    $sheet->addSheet('Sheet' . $sheetIndex++)->header([
                        'Tanggal Jurnal', 
                        'Jam Jurnal', 
                        'No Jurnal', 
                        'No Bukti', 
                        'Keterangan', 
                        'Keterangan Pengeluaran', 
                        'Catatan', 
                        'Kode Rek', 
                        'Nama Rek', 
                        'Debet', 
                        'Kredit'
                    ]);

                    $sheet->data(array_slice($rows, $remainingRows));

                    // Update the current row count
                    $currentRowCount = count($rows) - $remainingRows;
                } else {
                    // Add rows to the current sheet
                    $sheet->data($rows);

                    // Update the current row count
                    $currentRowCount += count($rows);
                }
            });

        $excel->output();

        TemporaryExport::where('batch_id', $this->batchId)->delete();

        $exportedFilename = "{$this->basePath}/{$filename}";

        $user = User::findByNRP($this->userId);

        Notification::send($user, new ExportReadyNotification($user, $exportedFilename));
    }
}