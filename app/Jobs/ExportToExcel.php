<?php

namespace App\Jobs;

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

    public function __construct(
        protected string $userId,
        protected string $exportSessionId,
        protected string $tglAwal,
        protected string $tglAkhir,
        protected string $kodeRekening,
        protected array $columnHeaders,
        protected int $chunkSize = 250,
    ) {
        $this->onQueue('exports');
    }

    public function handle()
    {
        Bus::chain([
            new InsertToTemporary(
                userId: $this->userId, 
                exportSessionId: $this->exportSessionId, 
                tglAwal: $this->tglAwal, 
                tglAkhir: $this->tglAkhir, 
                kodeRekening: $this->kodeRekening
            ),
            new PrepareExport(
                userId: $this->userId,
                exportSessionId: $this->exportSessionId,
                columnHeaders: $this->columnHeaders,
                chunkSize: $this->chunkSize,
            ),
            new WriteExcel(
                userId: $this->userId,
                exportSessionId: $this->exportSessionId,
            )
        ])->onQueue('exports')->dispatch();
    }
}