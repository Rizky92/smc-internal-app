<?php

namespace App\Services\Export;

use App\Models\ExportSession;

class ExportSessionService
{
    private string $userId;

    private string $exportSessionId;

    public function __construct(string $userId, string $exportSessionId)
    {
        $this->userId = $userId;
        $this->exportSessionId = $exportSessionId;
    }

    public function create(): void
    {
        ExportSession::create([
            'id'             => $this->exportSessionId,
            'user_id'        => $this->userId,
            'status'         => 'pending',
            'total_jobs'     => 0,
            'completed_jobs' => 0,
        ]);
    }

    public function markInserting(): void
    {
        $this->update(['status' => 'inserting']);
    }

    public function markPreparing(): void
    {
        $this->update(['status' => 'preparing']);
    }

    public function markExporting(int $totalJobs): void
    {
        $this->update([
            'status'         => 'exporting',
            'total_jobs'     => $totalJobs,
            'completed_jobs' => 0,
        ]);
    }

    public function markMerging(): void
    {
        $this->update(['status' => 'merging']);
    }

    public function markDone(string $filePath): void
    {
        $this->update([
            'status'    => 'done',
            'file_path' => $filePath,
        ]);
    }

    public function markFailed(string $errorMessage = ''): void
    {
        $this->update([
            'status'        => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function incrementCompletedJobs(): void
    {
        ExportSession::where('id', $this->exportSessionId)
            ->where('user_id', $this->userId)
            ->increment('completed_jobs');
    }

    protected function update(array $data): void
    {
        ExportSession::where('id', $this->exportSessionId)
            ->where('user_id', $this->userId)
            ->update($data);
    }
}
