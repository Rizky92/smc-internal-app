<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityIndicatorProfile extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicator_profiles';

    protected $fillable = [
        'quality_indicator_category_id',
        'quality_indicator_input_type_id',
        'title',
        'dimension',
        'objective',
        'definition',
        'inclusion',
        'exclusion',
        'frequency',
        'analysis_period',
        'numerator',
        'denominator',
        'standard',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(QualityIndicatorCategory::class, 'quality_indicator_category_id');
    }

    public function inputType(): BelongsTo
    {
        return $this->belongsTo(QualityIndicatorInputType::class, 'quality_indicator_input_type_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(QualityIndicator::class, 'quality_indicator_profile_id');
    }
}
