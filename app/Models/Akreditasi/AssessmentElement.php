<?php

namespace App\Models\Akreditasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssessmentElement extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'akreditasi_assessment_elements';

    protected $fillable = [
        'standard_id', 'kode', 'deskripsi',
        'penjelasan_kelengkapan_bukti', 'proof_method_id', 'urutan',
    ];

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class, 'standard_id');
    }

    public function proofMethod(): BelongsTo
    {
        return $this->belongsTo(ProofMethod::class, 'proof_method_id');
    }

    public function assessmentDocuments(): HasMany
    {
        return $this->hasMany(AssessmentDocument::class, 'assessment_element_id');
    }

    public function assessmentScore(): HasOne
    {
        return $this->hasOne(AssessmentScore::class, 'assessment_element_id');
    }
}
