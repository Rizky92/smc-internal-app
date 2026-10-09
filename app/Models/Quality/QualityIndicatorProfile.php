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

    protected function searchColumns(): array
    {
        return ['title'];
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
