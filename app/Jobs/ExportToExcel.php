<?php

namespace App\Jobs;

use App\Services\Export\ExportSessionService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class ExportToExcel implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $userId;

    private string $exportSessionId;

    private string $tglAwal;

    private string $tglAkhir;

    private string $kodeRekening;

    private array $columnHeaders;

    private int $chunkSize = 250;

    /**
     * @param  array{
     *      userId: string,
     *      exportSessionId: string,
     *      tglAwal: string,
     *      tglAkhir: string,
     *      kodeRekening: string,
     *      columnHeaders: array,
     *      chunkSize: int,
     * }  $params
     */
    public function __construct(
        array $params
    ) {
        $this->userId = $params['userId'];
        $this->exportSessionId = $params['exportSessionId'];
        $this->tglAwal = $params['tglAwal'];
        $this->tglAkhir = $params['tglAkhir'];
        $this->kodeRekening = $params['kodeRekening'];
        $this->columnHeaders = $params['columnHeaders'];
        $this->chunkSize = $params['chunkSize'] ?? 250;
    }

    public function handle(): void
    {
        /** @disregard Undefined type - class exists in App\Services\Export\ExportSessionService */
        $sessionService = new ExportSessionService($this->userId, $this->exportSessionId);
        $sessionService->create();

        Bus::chain([
            new InsertToTemporary(
                [
                    'userId'          => $this->userId,
                    'exportSessionId' => $this->exportSessionId,
                    'tglAwal'         => $this->tglAwal,
                    'tglAkhir'        => $this->tglAkhir,
                    'kodeRekening'    => $this->kodeRekening,
                ]
            ),
            new PrepareExport(
                [
                    'userId'          => $this->userId,
                    'exportSessionId' => $this->exportSessionId,
                    'columnHeaders'   => $this->columnHeaders,
                    'chunkSize'       => $this->chunkSize,
                ]
            ),
            new WriteExcel(
                [
                    'userId'          => $this->userId,
                    'exportSessionId' => $this->exportSessionId,
                ]
            ),
        ])->onQueue('exports')->dispatch();
    }
}
