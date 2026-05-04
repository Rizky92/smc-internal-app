<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityIndicatorRecord extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicator_records';

    protected $primaryKey = ['indicator_id', 'recorded_date'];

    public $incrementing = false;

    protected $fillable = [
        'indicator_id',
        'recorded_date',
        'notes',
        'numerator_value',
        'denominator_value',
        'recorded_by',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(QualityIndicator::class, 'indicator_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
