<?php

namespace App\Models\Akreditasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentScoreHistory extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'akreditasi_assessment_score_histories';

    protected $fillable = [
        'assessment_element_id', 'skor_lama', 'skor_baru',
        'catatan', 'changed_by',
    ];

    public function assessmentElement(): BelongsTo
    {
        return $this->belongsTo(AssessmentElement::class, 'assessment_element_id');
    }
}
