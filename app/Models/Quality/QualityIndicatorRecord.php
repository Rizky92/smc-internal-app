<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityIndicatorRecord extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicator_records';

    public $incrementing = false;

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
}
