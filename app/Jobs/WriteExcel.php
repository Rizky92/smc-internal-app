<?php

namespace App\Jobs;

use App\Services\Export\ExportCleanupService;
use App\Services\Export\ExportNotificationService;
use App\Services\Export\ExportSessionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader as CsvReader;
use League\Csv\Statement;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class WriteExcel implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 3600;

    private string $userId;

    private string $exportSessionId;

    /**
     * @param  array{
     *      userId: string,
     *      exportSessionId: string,
     * }  $params
     */
    public function __construct(
        array $params
    ) {
        $this->userId = $params['userId'];
        $this->exportSessionId = $params['exportSessionId'];
    }

    public function handle(): void
    {
        $disk = $this->getFileDisk();

        $fileName = now()->format('Y-m-d_H-i-s')."_{$this->userId}_{$this->exportSessionId}.xlsx";

        $writer = app(Writer::class);
        $writer->openToFile($temporaryFile = tempnam(sys_get_temp_dir(), $fileName));

        $csvDelimiter = ',';

        $writeRowsFromFile = function (string $file) use ($csvDelimiter, $disk, $writer): void {
            $csvReader = CsvReader::createFromStream($disk->readStream($file));
            $csvReader->setDelimiter($csvDelimiter);
            $csvResults = (new Statement)->process($csvReader);

            foreach ($csvResults->getRecords() as $row) {
                $writer->addRow(Row::fromValues($row));
            }
        };

        $writeRowsFromFile($this->getFileDirectory().'/headers.csv');

        foreach ($disk->files($this->getFileDirectory()) as $file) {
            if (str($file)->endsWith('headers.csv')) {
                continue;
            }

            if (! str($file)->endsWith('.csv')) {
                continue;
            }

            $writeRowsFromFile($file);
        }

        $writer->close();

        $disk->putFileAs(
            $this->getFileDirectory(),
            new File($temporaryFile),
            $fileName
        );

        unlink($temporaryFile);

        $this->resolveCleanupService()->cleanDatabase();

        $this->resolveSessionService()->markDone($this->getFileDirectory().'/'.$fileName);

        $this->resolveNotificationService()->notifyReady($this->getFileDirectory().'/'.$fileName);
    }

    public function getFileDirectory(): string
    {
        return "exports/{$this->userId}/{$this->exportSessionId}";
    }

    public function getFileDisk(): FilesystemAdapter
    {
        return Storage::disk('local');
    }

    public function failed(\Throwable $exception): void
    {
        $this->resolveCleanupService()->cleanDatabase();
        $this->resolveNotificationService()->notifyFailed();
    }

    protected function resolveCleanupService(): ExportCleanupService
    {
        return new ExportCleanupService($this->userId, $this->exportSessionId);
    }

    protected function resolveNotificationService(): ExportNotificationService
    {
        return new ExportNotificationService($this->userId);
    }

    protected function resolveSessionService(): ExportSessionService
    {
        return new ExportSessionService($this->userId, $this->exportSessionId);
    }
}
