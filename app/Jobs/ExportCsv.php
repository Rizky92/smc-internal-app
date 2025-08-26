<?php

namespace App\Jobs;

use App\Models\Export;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use SplTempFileObject;

class ExportCsv
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $userId,
        protected string $exportSessionId,
        protected array $records,
        protected int $page
    ) {}

    public function handle()
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject);
        $csv->setDelimiter(',');

        $query = Export::select([
            'column1',
            'column2',
            'column3',
            'column4',
            'column5',
            'column6',
            'column7',
            'column8',
            'column9',
        ])
        ->where('export_session_id', $this->exportSessionId)
        ->where('id_user', $this->userId);

        foreach ($query->find($this->records) as $record) {
            $csv->insertOne($record->toArray());
        }

        $filePath = "exports/{$this->userId}/{$this->exportSessionId}/" . str_pad(strval($this->page), 16, '0', STR_PAD_LEFT) . '.csv';
        Storage::disk('local')->put($filePath, $csv->toString());
    }
}