<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use App\Models\Bidang;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityIndicator extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicators';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'bidang_id',
        'quality_indicator_category_id',
        'sort_order',
        'title',
        'dimension',
        'objective',
        'definition',
        'inclusion',
        'exclusion',
        'frequency',
        'quality_indicator_input_type_id',
        'analysis_period',
        'numerator',
        'denominator',
        'data_source',
        'standard',
        'person_in_charge',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(QualityIndicatorCategory::class, 'quality_indicator_category_id');
    }

    public function inputType(): BelongsTo
    {
        return $this->belongsTo(QualityIndicatorInputType::class, 'quality_indicator_input_type_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }
}
