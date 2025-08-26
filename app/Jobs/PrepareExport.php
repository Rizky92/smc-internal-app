<?php

namespace App\Jobs;

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

    public function __construct(
        protected string $userId,
        protected string $exportSessionId,
        protected array $columnHeaders,
        protected int $chunkSize = 100,
        protected ?array $records = null,
    ) {}

    public function handle()
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject);
        $csv->setDelimiter(',');
        $csv->insertOne($this->columnHeaders);

        $filePath = "exports/{$this->userId}/{$this->exportSessionId}/headers.csv";
        Storage::disk('local')->put($filePath, $csv->toString());

        $exportCsvJob = $this->getExportCsvJob();

        $page = 1;

        $dispatchRecords = function (array $records) use ($exportCsvJob, &$page) {
            $jobs = [];

            foreach (array_chunk($records, $this->chunkSize) as $recordChunk) {
                $jobs[] = app($exportCsvJob, [
                    'userId' => $this->userId,
                    'exportSessionId' => $this->exportSessionId,
                    'records' => $recordChunk,
                    'page' => $page
                ]);

                $page++;
            }

            Bus::batch($jobs)->dispatch();
        };

        $chunkKeySize = $this->chunkSize * 10;

        $baseQuery = DB::connection('mysql_smc')
            ->table('exports')
            ->where('export_session_id', $this->exportSessionId)
            ->where('id_user', $this->userId);

        $baseQuery->select(['row_index'])
            ->chunkById($chunkKeySize, fn( Collection $records) => $dispatchRecords(
                Arr::pluck($records->all(), 'row_index')
            ), 'row_index');
    }

    public function getExportCsvJob()
    {
        return ExportCsv::class;
    }
}