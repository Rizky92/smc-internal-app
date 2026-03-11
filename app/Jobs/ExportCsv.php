<?php

namespace App\Jobs;

use App\Models\Export;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

    private string $userId;

    private string $exportSessionId;

    private string $exportName;

    private array $records;

    private int $page;

    /**
     * @param  array{
     *      exportSessionId: string,
     *      exportName: string,
     *      userId: string,
     *      records: array,
     *      page: int,
     * }  $params
     */
    public function __construct(array $params)
    {
        $this->exportSessionId = $params['exportSessionId'];
        $this->exportName = $params['exportName'];
        $this->userId = $params['userId'];
        $this->records = $params['records'];
        $this->page = $params['page'];
    }

    public function handle(): void
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
            ->where('export_name', $this->exportName)
            ->where('id_user', $this->userId);

        foreach ($query->find($this->records) as $record) {
            $csv->insertOne($record->toArray());
        }

        $filePath = "exports/{$this->userId}/{$this->exportSessionId}/".str_pad(strval($this->page), 16, '0', STR_PAD_LEFT).'.csv';
        Storage::disk('public')->put($filePath, $csv->toString());
    }
}
