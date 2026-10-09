<?php

namespace App\Models\Quality;

use App\Database\Eloquent\Model;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityIndicatorProfile extends Model
{
    /**
     * Periode analisis baku (bulan) yang selaras dengan kalender.
     */
    public const ANALYSIS_PERIODS = [
        1  => 'Bulanan',
        3  => 'Triwulan',
        6  => 'Semester',
        12 => 'Tahunan',
    ];

    private const PERIOD_TYPES = [
        1  => 'monthly',
        3  => 'quarterly',
        6  => 'semester',
        12 => 'yearly',
    ];

    public const TARGET_OPERATORS = [
        'gte' => '≥',
        'lte' => '≤',
    ];

    public const STATUS_TERCAPAI = 'tercapai';

    public const STATUS_TIDAK_TERCAPAI = 'tidak_tercapai';

    public const STATUS_BELUM_DINILAI = 'belum_dinilai';

    /**
     * Label dan varian badge untuk status capaian.
     */
    public const ACHIEVEMENT_BADGES = [
        self::STATUS_TERCAPAI       => ['Tercapai', 'success'],
        self::STATUS_TIDAK_TERCAPAI => ['Tidak Tercapai', 'danger'],
        self::STATUS_BELUM_DINILAI  => ['Belum Dinilai', 'secondary'],
    ];

    protected $connection = 'mysql_smc';

    protected $table = 'quality_indicator_profiles';

    protected $fillable = [
        'quality_indicator_category_id',
        'quality_indicator_input_type_id',
        'title',
        'dimension',
        'objective',
        'definition',
        'inclusion',
        'exclusion',
        'frequency',
        'analysis_period',
        'numerator',
        'denominator',
        'standard',
        'target_operator',
        'target_value',
        'rationale',
        'indicator_type',
        'measurement_unit',
        'formula',
        'data_collection_method',
        'instrument',
        'sample_size',
        'sampling_method',
        'data_presentation',
    ];

    protected $casts = [
        'target_value' => 'float',
    ];

    protected function searchColumns(): array
    {
        return ['title'];
    }

    /**
     * Status capaian terhadap target terstruktur. Tanpa operator atau nilai target, atau tanpa capaian
     * (ΣD = 0 / tidak ada data), hasilnya belum bisa dinilai: tidak ada asumsi arah ≥.
     *
     * Method murni (tanpa query), diuji di tests/Unit.
     */
    public function achievementStatus(?float $achievement): string
    {
        if ($achievement === null || $this->target_value === null || ! isset(self::TARGET_OPERATORS[$this->target_operator])) {
            return self::STATUS_BELUM_DINILAI;
        }

        $achievement = round($achievement, 2);

        $tercapai = $this->target_operator === 'gte'
            ? $achievement >= $this->target_value
            : $achievement <= $this->target_value;

        return $tercapai ? self::STATUS_TERCAPAI : self::STATUS_TIDAK_TERCAPAI;
    }

    /**
     * "Target ≥ 85%", atau null bila target belum terstruktur.
     */
    public function targetLabel(): ?string
    {
        if ($this->target_value === null || ! isset(self::TARGET_OPERATORS[$this->target_operator])) {
            return null;
        }

        return sprintf('Target %s %s%%', self::TARGET_OPERATORS[$this->target_operator], rtrim(rtrim(number_format($this->target_value, 2, '.', ''), '0'), '.'));
    }

    /**
     * Periode analisis kalender yang memuat tanggal itu: bulan, triwulan (Jan–Mar, …), semester, atau tahun.
     * Periode kosong atau nilai lama di luar ANALYSIS_PERIODS diperlakukan bulanan; nilai lama itu
     * dilaporkan `mutu:audit-profil` dan ditolak form saat profil disimpan ulang.
     *
     * Method murni (tanpa query), diuji di tests/Unit.
     *
     * @return array{type: string, start: CarbonImmutable, end: CarbonImmutable}
     */
    public function periodFor(CarbonInterface $date): array
    {
        $months = array_key_exists((int) $this->analysis_period, self::PERIOD_TYPES)
            ? (int) $this->analysis_period
            : 1;

        $date = CarbonImmutable::instance($date);
        $startMonth = intdiv($date->month - 1, $months) * $months + 1;
        $start = $date->setDate($date->year, $startMonth, 1)->startOfDay();

        return [
            'type'  => self::PERIOD_TYPES[$months],
            'start' => $start,
            'end'   => $start->addMonthsNoOverflow($months - 1)->endOfMonth(),
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(QualityIndicatorCategory::class, 'quality_indicator_category_id');
    }

    public function inputType(): BelongsTo
    {
        return $this->belongsTo(QualityIndicatorInputType::class, 'quality_indicator_input_type_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(QualityIndicator::class, 'quality_indicator_profile_id');
    }
}
