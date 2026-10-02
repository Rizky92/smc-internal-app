<?php

namespace App\Models\Akreditasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Standard extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'akreditasi_standards';

    protected $fillable = ['focus_area_id', 'kode', 'judul', 'maksud_tujuan', 'urutan'];

    public function focusArea(): BelongsTo
    {
        return $this->belongsTo(FocusArea::class, 'focus_area_id');
    }

    public function assessmentElements(): HasMany
    {
        return $this->hasMany(AssessmentElement::class, 'standard_id');
    }
}
