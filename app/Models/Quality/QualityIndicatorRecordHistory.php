<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris per transisi validasi record. Nilai yang disimpan adalah nilai record setelah transisi.
 */
class QualityIndicatorRecordHistory extends Model
{
    public const ACTION_SUBMITTED = 'submitted';

    public const ACTION_APPROVED = 'approved';

    public const ACTION_REJECTED = 'rejected';

    public const ACTION_CORRECTED = 'corrected';

    public const ACTION_RESET = 'reset';

    public const ACTION_LABELS = [
        self::ACTION_SUBMITTED => 'Diserahkan',
        self::ACTION_APPROVED  => 'Disetujui',
        self::ACTION_REJECTED  => 'Ditolak',
        self::ACTION_CORRECTED => 'Dikoreksi validator',
        self::ACTION_RESET     => 'Batal validasi',
    ];

    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicator_record_histories';

    public $timestamps = false;

    protected $fillable = [
        'action',
        'status_before',
        'status_after',
        'numerator_value',
        'denominator_value',
        'notes',
        'reason',
        'actor',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (self $history): void {
            $history->created_at = $history->created_at ?? now();
        });
    }

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(QualityIndicatorRecord::class, 'record_id');
    }
}
