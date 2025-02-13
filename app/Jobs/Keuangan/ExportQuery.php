<?php

namespace App\Jobs\Keuangan;

use App\EloquentSerialize\Facades\EloquentSerializeFacade;
use App\Models\Aplikasi\User;
use App\Notifications\ExportReadyNotification;
use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Laravel\SerializableClosure\SerializableClosure;
use Vtiful\Kernel\Excel;

class ExportQuery implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Summary of filename
     *
     * @var string
     */
    protected $filename;

    public $query;

    protected $columnHeaders;

    protected $userId;

    protected SerializableClosure $mapper;

    public function __construct(
        string $query, 
        array $columnHeaders, 
        Closure $mapper,
        string $userId  
    ) {
        $this->query = $query;
        $this->columnHeaders = $columnHeaders;
        $this->mapper = new SerializableClosure($mapper);
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $filename = now()->format('Ymd_His').'_export.xlsx';
        $path = storage_path('app/public/excel');
        File::ensureDirectoryExists($path);
        $config = ['path' => $path];
        $query = EloquentSerializeFacade::unserialize($this->query);
        $mapper = $this->mapper->getClosure();
        $sheetIndex = 1;
        $currentRowCount = 0;
        $maxRowsPerSheet = 1000000;
        $excel = (new Excel($config))->fileName($filename, "Sheet $sheetIndex")->header($this->columnHeaders);
        $query->chunk(5000, function (Collection $rows) use (&$data, &$sheetIndex, &$currentRowCount, &$maxRowsPerSheet, $excel, $mapper) {
            $data = $rows->map($mapper)->toArray();
            if ($currentRowCount + count($data) > $maxRowsPerSheet) {
                $remainingRows = $maxRowsPerSheet - $currentRowCount;
                $excel->data(array_slice($data, 0, $remainingRows));
                $sheetIndex++;
                $excel->addSheet('Sheet'.$sheetIndex++)->header($this->columnHeaders)->data(array_slice($data, $remainingRows));
                $excel->data(array_slice($data, $remainingRows));
                $currentRowCount = count($data) - $remainingRows;
            } else {
                $excel->data($data);
                $currentRowCount += count($data);
            }
        });
        $excel->output();
        $user = User::findByNRP($this->userId);
        Notification::send($user, new ExportReadyNotification($user, $filename));
    }
}
