<?php

namespace App\Models;

use App\Database\Eloquent\Model;

class ExportSession extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'export_sessions';

    public $incrementing = false;

    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'user_id',
        'status',
        'total_jobs',
        'completed_jobs',
        'file_path',
        'error_message',
    ];

    public function getProgressPercentageAttribute(): int
    {
        $progress = [
            'pending'   => 0,
            'inserting' => 15,
            'preparing' => 30,
            'exporting' => $this->total_jobs > 0
                            ? 30 + (int) round(($this->completed_jobs / $this->total_jobs) * 60)
                            : 30,
            'merging'   => 90,
        ];

        return $progress[$this->status] ?? 0;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInserting(): bool
    {
        return $this->status === 'inserting';
    }

    public function isPreparing(): bool
    {
        return $this->status === 'preparing';
    }

    public function isExporting(): bool
    {
        return $this->status === 'exporting';
    }

    public function isMerging(): bool
    {
        return $this->status === 'merging';
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending'   => 'Menunggu...',
            'inserting' => 'Memuat data...',
            'preparing' => 'Mempersiapkan export...',
            'exporting' => 'Mengekspor data...',
            'merging'   => 'Menggabungkan file...',
            'done'      => 'Selesai',
            'failed'    => 'Gagal',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }
}
