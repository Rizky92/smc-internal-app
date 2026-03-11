<?php

namespace App\Jobs;

use App\Models\Export;
use App\Models\Keuangan\Jurnal\Jurnal;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

    public $timeout = 3600;

    private string $exportSessionId;

    private string $exportName;

    private string $userId;

    private string $tglAwal;

    private string $tglAkhir;

    private string $kodeRekening;

    private array $columnHeaders;

    private int $chunkSize = 1000;

    /**
     * @param  array{
     *      exportSessionId: string,
     *      exportName: string,
     *      userId: string,
     *      tglAwal: string,
     *      tglAkhir: string,
     *      kodeRekening: string,
     *      columnHeaders: array,
     *      chunkSize: int
     * }  $params
     */
    public function __construct(array $params)
    {
        $this->exportSessionId = $params['exportSessionId'];
        $this->exportName = $params['exportName'];
        $this->userId = $params['userId'];
        $this->tglAwal = $params['tglAwal'];
        $this->tglAkhir = $params['tglAkhir'];
        $this->kodeRekening = $params['kodeRekening'] ?? '';
        $this->columnHeaders = $params['columnHeaders'];
        $this->chunkSize = $params['chunkSize'] ?? 1000;
    }

    public function handle(): void
    {
        $this->InsertToTemporary();
        $this->PrepareCsv();
    }

    public function PrepareCsv(): void
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject);
        $csv->setDelimiter(',');
        $csv->insertOne($this->columnHeaders);

        $filePath = "exports/{$this->userId}/{$this->exportSessionId}/headers.csv";
        Storage::disk('public')->put($filePath, $csv->toString());

        $exportCsvJob = $this->getExportCsvJob();

        $page = 1;

        $dispatchRecords = function (array $records) use ($exportCsvJob, &$page): void {
            $jobs = [];

            foreach (array_chunk($records, $this->chunkSize) as $recordChunk) {
                $jobs[] = new $exportCsvJob([
                    'exportSessionId' => $this->exportSessionId,
                    'exportName'      => $this->exportName,
                    'userId'          => $this->userId,
                    'records'         => $recordChunk,
                    'page'            => $page,
                ]);

                $page++;
            }

            $this->batch()->add($jobs);
        };

        $chunkKeySize = $this->chunkSize * 10;

        $baseQuery = DB::connection('mysql_smc')
            ->table('exports')
            ->where('export_session_id', $this->exportSessionId)
            ->where('export_name', $this->exportName)
            ->where('id_user', $this->userId);

        $baseQuery
            ->select(['id'])
            ->chunkById(
                $chunkKeySize,
                fn (Collection $records) => $dispatchRecords(
                    Arr::pluck($records->all(), 'id')
                ), 'id');
    }

    public function InsertToTemporary(): void
    {
        Export::query()->where('export_name', $this->exportName)->where('id_user', $this->userId)->delete();

        $query = Jurnal::on('mysql_sik')
            ->select(DB::raw("'$this->exportSessionId' as export_session_id"),
                DB::raw("'$this->exportName' as export_name"),
                DB::raw("'$this->userId' as id_user"),
                'jurnal.tgl_jurnal',
                'jurnal.jam_jurnal',
                'jurnal.no_jurnal',
                'jurnal.no_bukti',
                'jurnal.keterangan',
                'detailjurnal.kd_rek',
                'rekening.nm_rek',
                'detailjurnal.debet',
                'detailjurnal.kredit'
            )
            ->join('sik.detailjurnal', 'jurnal.no_jurnal', '=', 'detailjurnal.no_jurnal')
            ->join('sik.rekening', 'detailjurnal.kd_rek', '=', 'rekening.kd_rek')
            ->when(! empty($this->kodeRekening), fn (Builder $q) => $q->where('detailjurnal.kd_rek', $this->kodeRekening))
            ->whereBetween('jurnal.tgl_jurnal', [$this->tglAwal, $this->tglAkhir])
            ->orderBy('jurnal.tgl_jurnal', 'asc')
            ->orderBy('jurnal.jam_jurnal', 'asc')
            ->orderBy('jurnal.no_jurnal', 'asc');

        DB::connection('mysql_smc')->table('exports')->insertUsing([
            'export_session_id',
            'export_name',
            'id_user',
            'column1',
            'column2',
            'column3',
            'column4',
            'column5',
            'column6',
            'column7',
            'column8',
            'column9',
        ], $query->toBase());
    }

    /**
     * @psalm-return ExportCsv::class
     */
    public function getExportCsvJob(): string
    {
        return ExportCsv::class;
    }
}
