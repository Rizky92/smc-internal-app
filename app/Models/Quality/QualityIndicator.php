<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use App\Models\Bidang;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityIndicator extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicators';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'quality_indicator_profile_id',
        'bidang_id',
        'person_in_charge',
        'data_source',
        'status',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(QualityIndicatorProfile::class, 'quality_indicator_profile_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(QualityIndicatorRecord::class, 'indicator_id');
    }
}
