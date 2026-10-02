<?php

namespace App\Models\Akreditasi;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentDocument extends Model
{
    protected $connection = 'mysql_smc';

    protected $table = 'akreditasi_assessment_documents';

    protected $fillable = [
        'assessment_element_id', 'judul_dokumen', 'keterangan',
        'file_path', 'file_size', 'mime_type', 'uploaded_by',
    ];

    public function assessmentElement(): BelongsTo
    {
        return $this->belongsTo(AssessmentElement::class, 'assessment_element_id');
    }
}
