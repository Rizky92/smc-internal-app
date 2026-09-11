<?php

namespace App\Jobs\Concerns;

use App\Models\ExportSession;

trait HandlesExportSession
{
    protected string $exportSessionId;

    protected string $exportName;

    protected string $userId;

    /**
     * Update status export session.
     */
    protected function updateSessionStatus(string $status): void
    {
        ExportSession::query()
            ->where('session_id', $this->exportSessionId)
            ->where('id_user', $this->userId)
            ->where('export_name', $this->exportName)
            ->update(['status' => $status]);
    }
}
