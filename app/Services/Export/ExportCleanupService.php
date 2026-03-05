<?php

namespace App\Services\Export;

use Illuminate\Support\Facades\DB;

class ExportCleanupService
{
    private string $userId;

    private string $exportSessionId;

    public function __construct(
        string $userId,
        string $exportSessionId
    ) {
        $this->userId = $userId;
        $this->exportSessionId = $exportSessionId;
    }

    public function cleanDatabase(): void
    {
        DB::connection('mysql_smc')
            ->table('exports')
            ->where('export_session_id', $this->exportSessionId)
            ->where('id_user', $this->userId)
            ->delete();
    }
}
