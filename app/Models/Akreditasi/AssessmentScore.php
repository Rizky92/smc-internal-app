<?php

namespace App\Models\Akreditasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentScore extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'akreditasi_assessment_scores';

    protected $fillable = [
        'assessment_element_id', 'skor', 'nilai',
        'catatan', 'assessed_by', 'assessed_at',
    ];

    public function assessmentElement(): BelongsTo
    {
        return $this->belongsTo(AssessmentElement::class, 'assessment_element_id');
    }
}
