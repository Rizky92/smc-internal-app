<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengajuan koreksi petugas atas record yang sudah disetujui. Nilai di sini baru berlaku setelah disetujui validator.
 */
class QualityIndicatorCorrectionRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicator_correction_requests';

    protected $fillable = [
        'numerator_value',
        'denominator_value',
        'notes',
        'reason',
        'requested_by',
        'status',
        'reviewed_by',
        'review_reason',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(QualityIndicatorRecord::class, 'record_id');
    }
}
