<?php

namespace Database\Factories\Quality;

use App\Models\Quality\QualityIndicatorRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class QualityIndicatorRecordFactory extends Factory
{
    protected $model = QualityIndicatorRecord::class;

    public function definition(): array
    {
        return [
            'indicator_id'      => QualityIndicatorFactory::new(),
            'recorded_date'     => now()->format('Y-m-d'),
            'numerator_value'   => 8,
            'denominator_value' => 10,
            'notes'             => null,
            'recorded_by'       => null,
            'status'            => 'draft',
        ];
    }
}
