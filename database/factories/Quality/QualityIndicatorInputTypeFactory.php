<?php

namespace Database\Factories\Quality;

use App\Models\Quality\QualityIndicatorInputType;
use Illuminate\Database\Eloquent\Factories\Factory;

class QualityIndicatorInputTypeFactory extends Factory
{
    protected $model = QualityIndicatorInputType::class;

    public function definition(): array
    {
        return [
            'name' => 'Tipe Input '.$this->faker->unique()->numerify('####'),
        ];
    }
}
