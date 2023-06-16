<?php

namespace Database\Factories\Keuangan\RKAT;

use Illuminate\Database\Eloquent\Factories\Factory;

class AnggaranDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama'     => $this->faker->words(rand(1, 5), true),
            'subtotal' => $this->faker->numberBetween(100_000, 99_999_999),
        ];
    }
}
