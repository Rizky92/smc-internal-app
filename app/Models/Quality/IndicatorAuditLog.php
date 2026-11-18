<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorAuditLog extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'indikator_harian_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'indicator_id',
        'recorded_date',
        'field_name',
        'old_value',
        'new_value',
        'changed_by',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (self $log): void {
            $log->created_at = $log->created_at ?? now();
        });
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(QualityIndicator::class, 'indicator_id');
    }
}
