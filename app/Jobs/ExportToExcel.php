<?php

namespace App\Jobs;

use App\Models\Aplikasi\User;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Notification;
use Rizky92\Xlswriter\ExcelExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportToExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $serializedQuery;
    
    protected $columnHeaders;

    protected $pageHeaders;

    protected $userId;

    /**
     * Create a new job instance.
     * 
     * @param SerializedQuery $serializedQuery
     * @param string $userId
     */
    public function __construct(SerializedQuery $serializedQuery, array $columnHeaders, array $pageHeaders, string $userId)
    {
        $this->serializedQuery = $serializedQuery;
        $this->columnHeaders = $columnHeaders;
        $this->pageHeaders = $pageHeaders;
        $this->userId = $userId;
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

        Notification::send($user, new ExportReadyNotification($user, $filePath));
    }

    protected function dataPerSheet(): array
    {
        return [
            $this->serializedQuery->connection => function () {
                return $this->serializedQuery->execute();
            },
        ];
    }

    protected function columnHeaders(): array
    {
        return $this->columnHeaders;
    }

    protected function pageHeaders(): array
    {
        return $this->pageHeaders;
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
