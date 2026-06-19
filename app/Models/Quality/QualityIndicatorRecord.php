<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityIndicatorRecord extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicator_records';

    protected $fillable = [
        'indicator_id',
        'recorded_date',
        'notes',
        'numerator_value',
        'denominator_value',
        'recorded_by',
        'status',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(QualityIndicator::class, 'indicator_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(IndicatorAuditLog::class, 'indicator_id', 'indicator_id')
            ->whereColumn('recorded_date', 'recorded_date');
    }
}
