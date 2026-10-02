<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class QualityIndicatorRecord extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_APPROVED_WITH_CORRECTION = 'approved_with_correction';

    /**
     * Dibatalkan validator. Record tetap ada sebagai histori dan tidak dihitung di laporan.
     */
    public const STATUS_VOIDED = 'voided';

    /**
     * Record yang datanya sudah divalidasi dan dipakai untuk menghitung capaian.
     */
    public const STATUSES_DISETUJUI = [self::STATUS_APPROVED, self::STATUS_APPROVED_WITH_CORRECTION];

    /**
     * Record yang sudah diserahkan petugas unit, baik yang belum maupun yang sudah divalidasi.
     */
    public const STATUSES_DILAPORKAN = [self::STATUS_SUBMITTED, ...self::STATUSES_DISETUJUI];

    /**
     * Record berstatus ini tidak boleh diubah maupun dihapus oleh petugas unit.
     * Record ditolak tetap bisa diperbaiki dan diserahkan ulang (ADR 0002).
     */
    public const STATUSES_TERKUNCI = [self::STATUS_SUBMITTED, self::STATUS_APPROVED, self::STATUS_APPROVED_WITH_CORRECTION, self::STATUS_VOIDED];

    /**
     * Pembatalan hanya untuk data yang sudah disetujui; data lain cukup ditolak.
     */
    public const STATUSES_BISA_DIVOID = self::STATUSES_DISETUJUI;

    public const STATUS_LABELS = [
        self::STATUS_DRAFT                    => 'Draft',
        self::STATUS_SUBMITTED                => 'Submitted',
        self::STATUS_APPROVED                 => 'Approved',
        self::STATUS_REJECTED                 => 'Rejected',
        self::STATUS_APPROVED_WITH_CORRECTION => 'Approved w/ Correction',
        self::STATUS_VOIDED                   => 'Voided',
    ];

    /**
     * Varian komponen `<x-badge>` per status.
     */
    public const STATUS_BADGES = [
        self::STATUS_DRAFT                    => 'secondary',
        self::STATUS_SUBMITTED                => 'info',
        self::STATUS_APPROVED                 => 'success',
        self::STATUS_REJECTED                 => 'danger',
        self::STATUS_APPROVED_WITH_CORRECTION => 'primary',
        self::STATUS_VOIDED                   => 'dark',
    ];

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
        return in_array($this->status, self::STATUSES_TERKUNCI, true);
    }

    /**
     * Hapus permanen hanya untuk draft yang belum pernah diserahkan; histori validasi tidak boleh hilang.
     */
    public function canBeDeleted(): bool
    {
        return $this->status === self::STATUS_DRAFT && ! $this->histories()->exists();
    }

    protected function searchColumns(): array
    {
        return [
            DB::raw('(select quality_indicator_profiles.title from quality_indicator_profiles join quality_indicators on quality_indicators.quality_indicator_profile_id = quality_indicator_profiles.id where quality_indicators.id = quality_indicator_records.indicator_id)'),
            'notes',
        ];
    }

    /**
     * Status kosong atau tidak dikenal ditampilkan sebagai draft.
     */
    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? self::STATUS_LABELS[self::STATUS_DRAFT];
    }

    public function statusBadgeVariant(): string
    {
        return self::STATUS_BADGES[$this->status] ?? self::STATUS_BADGES[self::STATUS_DRAFT];
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

    public function histories(): HasMany
    {
        return $this->hasMany(QualityIndicatorRecordHistory::class, 'record_id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(QualityIndicatorCorrectionRequest::class, 'record_id');
    }

    /**
     * Paling banyak satu pengajuan pending per record (dijaga saat mengajukan).
     */
    public function pendingCorrection(): HasOne
    {
        return $this->hasOne(QualityIndicatorCorrectionRequest::class, 'record_id')
            ->where('status', QualityIndicatorCorrectionRequest::STATUS_PENDING);
    }

    /**
     * Catat transisi validasi dengan nilai record saat ini dan user yang sedang login sebagai pelaku.
     */
    public function recordHistory(string $action, ?string $statusBefore, ?string $reason = null): QualityIndicatorRecordHistory
    {
        /** @var QualityIndicatorRecordHistory */
        $history = $this->histories()->create([
            'action'            => $action,
            'status_before'     => $statusBefore,
            'status_after'      => $this->status,
            'numerator_value'   => $this->numerator_value,
            'denominator_value' => $this->denominator_value,
            'notes'             => $this->notes,
            'reason'            => $reason,
            'actor'             => user()->nik,
        ]);

        return $history;
    }

    /**
     * Record hasil koreksi lama belum punya histori, tetapi punya audit log.
     * Muat `withCount('histories')` di daftar agar tidak ada query per baris.
     */
    public function punyaRiwayat(): bool
    {
        if ($this->status === self::STATUS_APPROVED_WITH_CORRECTION) {
            return true;
        }

        return (int) ($this->histories_count ?? $this->histories()->count()) > 0;
    }

    /**
     * Alasan penolakan terakhir, untuk ditampilkan ke petugas yang memperbaiki record.
     */
    public function lastRejectionReason(): ?string
    {
        return $this->histories()
            ->where('action', QualityIndicatorRecordHistory::ACTION_REJECTED)
            ->latest('created_at')
            ->latest('id')
            ->value('reason');
    }
}
