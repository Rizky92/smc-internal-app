<?php

namespace App\Jobs;

use App\Services\Export\ExportCleanupService;
use App\Services\Export\ExportNotificationService;
use App\Services\Export\ExportSessionService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use SplTempFileObject;

class PrepareExport implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;

    private string $userId;

    private string $exportSessionId;

    private array $columnHeaders;

    private int $chunkSize = 2500;

    public function __construct(array $params)
    {
        $this->userId = $params['userId'];
        $this->exportSessionId = $params['exportSessionId'];
        $this->columnHeaders = $params['columnHeaders'];
        $this->chunkSize = $params['chunkSize'] ?? 2500;
    }

    public function handle(): void
    {
        $this->resolveSessionService()->markPreparing();

        $csv = Writer::createFromFileObject(new SplTempFileObject);
        $csv->setDelimiter(',');
        $csv->insertOne($this->columnHeaders);

        $filePath = "exports/{$this->userId}/{$this->exportSessionId}/headers.csv";
        Storage::disk('local')->put($filePath, $csv->toString());

        $exportCsvJob = $this->getExportCsvJob();

        $page = 1;
        $totalJobs = 0;
        $chunkKeySize = $this->chunkSize * 10;

        $baseQuery = DB::connection('mysql_smc')
            ->table('exports')
            ->where('export_session_id', $this->exportSessionId)
            ->where('id_user', $this->userId);

        // Calculate total jobs first
        $totalRecords = (clone $baseQuery)->count();
        $totalJobs = (int) ceil($totalRecords / $this->chunkSize);

        // Mark exporting with total jobs count
        $this->resolveSessionService()->markExporting($totalJobs);

        $dispatchRecords = function (array $records) use ($exportCsvJob, &$page): void {
            $jobs = [];

            foreach (array_chunk($records, $this->chunkSize) as $recordChunk) {
                $jobs[] = new $exportCsvJob([
                    'userId'          => $this->userId,
                    'exportSessionId' => $this->exportSessionId,
                    'records'         => $recordChunk,
                    'page'            => $page,
                ]);

                $page++;
            }

            Bus::batch($jobs)->onQueue('exports')->dispatch();
        };

        $baseQuery
            ->select(['id'])
            ->chunkById(
                $chunkKeySize,
                fn (Collection $records) => $dispatchRecords(
                    Arr::pluck($records->all(), 'id')
                ), 'id');
    }

    public function failed(\Throwable $exception): void
    {
        $this->resolveCleanupService()->cleanDatabase();
        $this->resolveNotificationService()->notifyFailed();
        $this->resolveSessionService()->markFailed();
    }

    /**
     * @psalm-return ExportCsv::class
     */
    public function getExportCsvJob(): string
    {
        return ExportCsv::class;
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
