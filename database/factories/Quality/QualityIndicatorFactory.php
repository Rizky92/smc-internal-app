<?php

namespace Database\Factories\Quality;

use App\Models\Quality\QualityIndicator;
use Illuminate\Database\Eloquent\Factories\Factory;

class QualityIndicatorFactory extends Factory
{
    protected $model = QualityIndicator::class;

    public function definition(): array
    {
        return [
            'quality_indicator_profile_id' => QualityIndicatorProfileFactory::new(),
            'dep_id'                       => 'IT',
            'person_in_charge'             => $this->faker->name(),
            'data_source'                  => 'Rekam medis',
            'status'                       => 'active',
        ];
    }
}
