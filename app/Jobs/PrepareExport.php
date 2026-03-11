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
use League\Csv\Bom;
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
        protected int $chunkSize = 2500,
        protected ?array $records = null,
    ) {}

    public function handle(): void
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject);
        $csv->setOutputBOM(Bom::Utf8);
        $csv->setDelimiter(',');
        $csv->insertOne($this->columnHeaders);

        $filePath = "exports/{$this->userId}/{$this->exportSessionId}/headers.csv";
        Storage::disk('local')->put($filePath, $csv->toString());

        $exportCsvJob = $this->getExportCsvJob();

        $page = 1;

        $dispatchRecords = function (array $records) use ($exportCsvJob, &$page): void {
            $jobs = [];

            foreach (array_chunk($records, $this->chunkSize) as $recordChunk) {
                $jobs[] = app($exportCsvJob, [
                    'userId'          => $this->userId,
                    'exportSessionId' => $this->exportSessionId,
                    'records'         => $recordChunk,
                    'page'            => $page,
                ]);

                $page++;
            }

            Bus::batch($jobs)->onQueue('exports')->dispatch();
        };

        $chunkKeySize = $this->chunkSize * 10;

        $baseQuery = DB::connection('mysql_smc')
            ->table('exports')
            ->where('export_session_id', $this->exportSessionId)
            ->where('id_user', $this->userId);

        $baseQuery
            ->select(['id'])
            ->chunkById(
                $chunkKeySize,
                fn (Collection $records) => $dispatchRecords(
                    Arr::pluck($records->all(), 'id')
                ), 'id');
    }

    /**
     * @psalm-return ExportCsv::class
     */
    public function getExportCsvJob(): string
    {
        return ExportCsv::class;
    }
}
