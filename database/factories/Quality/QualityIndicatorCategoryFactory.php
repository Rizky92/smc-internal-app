<?php

namespace Database\Factories\Quality;

use App\Models\Quality\QualityIndicatorCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class QualityIndicatorCategoryFactory extends Factory
{
    protected $model = QualityIndicatorCategory::class;

    public function definition(): array
    {
        return [
            'name' => 'Kategori '.$this->faker->unique()->numerify('####'),
        ];
    }
}
