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

    /**
     * Record yang datanya sudah divalidasi dan dipakai untuk menghitung capaian.
     */
    public const STATUSES_DISETUJUI = [self::STATUS_APPROVED, self::STATUS_APPROVED_WITH_CORRECTION];

    /**
     * Record yang sudah diserahkan petugas unit, baik yang belum maupun yang sudah divalidasi.
     */
    public const STATUSES_DILAPORKAN = [self::STATUS_SUBMITTED, ...self::STATUSES_DISETUJUI];

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

    /**
     * Kolom diberi nama tabel agar scope tetap aman di query yang men-join `quality_indicators`.
     */
    public function scopePeriode(Builder $query, string $tglAwal, string $tglAkhir): Builder
    {
        return $query->whereBetween($this->qualifyColumn('recorded_date'), [$tglAwal, $tglAkhir]);
    }

    public function scopeDisetujui(Builder $query): Builder
    {
        return $query->whereIn($this->qualifyColumn('status'), self::STATUSES_DISETUJUI);
    }

    public function scopeDilaporkan(Builder $query): Builder
    {
        return $query->whereIn($this->qualifyColumn('status'), self::STATUSES_DILAPORKAN);
    }

    /**
     * @param  string|string[]  $depId
     */
    public function scopeDepartemen(Builder $query, $depId): Builder
    {
        return $query->whereHas('indicator', fn (Builder $q) => $q->departemen($depId));
    }

    /**
     * @param  int|string  $kategoriId
     */
    public function scopeKategori(Builder $query, $kategoriId): Builder
    {
        return $query->whereHas('indicator.profile', fn (Builder $q) => $q->where('quality_indicator_category_id', $kategoriId));
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(QualityIndicator::class, 'indicator_id');
    }

    /**
     * Audit log tidak punya foreign key ke record; pasangannya adalah indikator + tanggal.
     * Relasi ini hanya untuk satu record (tidak bisa di-eager load).
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(IndicatorAuditLog::class, 'indicator_id', 'indicator_id')
            ->where('recorded_date', $this->recorded_date);
    }
}
