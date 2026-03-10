<?php

namespace App\Jobs;

use App\Models\Aplikasi\User;
use App\Notifications\ExportFailedNotification;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Rizky92\Xlswriter\ExcelExport;

abstract class ExcelExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    
    public int $tries = 1;

    protected int $userId;
    
    protected array $payload;

    public function __construct(int $userId, array $payload) {
        $this->userId = $userId;
        $this->payload = $payload;
    }

    abstract protected function dataPerSheet(): array;
    abstract protected function columnHeaders(): array;
    abstract protected function pageHeaders(): array;
    abstract protected function filename(): string;

    public function handle(): void
    {
        $filename = now()->format('Ymd_His') . '_' . str($this->filename())->snake() . '.xlsx';

        $dataSheets = $this->dataPerSheet();
        $columnHeaders = $this->columnHeaders();

        $firstSheet = array_keys($dataSheets)[0] ?: 'Sheet 1';
        $firstData = Arr::isList($dataSheets)
            ? $dataSheets[0]
            : $dataSheets[$firstSheet];
        $firstData = is_callable($firstData) ? $firstData() : $firstData;

        $excel = ExcelExport::make($filename, $firstSheet)
            ->setPageHeaders($this->pageHeaders());

        if (Arr::isAssoc($columnHeaders)) {
            $excel->setColumnHeaders($columnHeaders[$firstSheet] ?? []);
        } else {
            $excel->setColumnHeaders($columnHeaders);
        }

        $excel->setData($firstData);

        array_shift($dataSheets);

        foreach ($dataSheets as $sheet => $data) {
            $data = is_callable($data) ? $data() : $data;
            $excel->addSheet($sheet);

            if (Arr::isAssoc($columnHeaders)) {
                $excel->setColumnHeaders($columnHeaders[$sheet] ?? []);
            }

            $excel->setData($data);
        }

        $filePath = $excel->save();

        $user = User::findByNRP($this->userId);
        $user->notify(new ExportReadyNotification($user, $filePath));
    }

    public function failed(\Throwable $exception): void
    {
        $user = User::findByNRP($this->userId);
        $user->notify(new ExportFailedNotification($user, null));
    }
}