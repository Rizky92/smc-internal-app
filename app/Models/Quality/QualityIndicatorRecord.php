<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityIndicatorRecord extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_APPROVED_WITH_CORRECTION = 'approved_with_correction';

    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicator_records';

    protected $fillable = [
        'indicator_id',
        'recorded_date',
        'notes',
        'numerator_value',
        'denominator_value',
        'recorded_by',
        'status',
    ];

    /**
     * Record yang sudah diserahkan atau disetujui tidak boleh diubah maupun dihapus oleh petugas unit.
     */
    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_APPROVED], true);
    }

    /**
     * Satu indikator hanya punya satu record per tanggal.
     */
    public function scopeTanggal(Builder $query, int $indicatorId, string $tanggal): Builder
    {
        return $query
            ->where('indicator_id', $indicatorId)
            ->where('recorded_date', $tanggal);
    }

    public function scopePeriode(Builder $query, string $tglAwal, string $tglAkhir): Builder
    {
        return $query
            ->whereBetween('recorded_date', [$tglAwal, $tglAkhir])
            ->orderBy('recorded_date');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(QualityIndicator::class, 'indicator_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(IndicatorAuditLog::class, 'indicator_id', 'indicator_id')
            ->whereColumn('recorded_date', 'recorded_date');
    }
}
