<?php

namespace Database\Factories\Quality;

use App\Models\Quality\QualityIndicatorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class QualityIndicatorProfileFactory extends Factory
{
    protected $model = QualityIndicatorProfile::class;

    public function definition(): array
    {
        return [
            'quality_indicator_category_id'   => QualityIndicatorCategoryFactory::new(),
            'quality_indicator_input_type_id' => QualityIndicatorInputTypeFactory::new(),
            'title'                           => 'Indikator '.$this->faker->unique()->numerify('####'),
            'frequency'                       => 'Harian',
            'standard'                        => '80%',
        ];
    }
}
