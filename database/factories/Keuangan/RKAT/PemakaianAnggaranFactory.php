<?php

namespace Database\Factories\Keuangan\RKAT;

use Illuminate\Database\Eloquent\Factories\Factory;

class PemakaianAnggaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama'        => $this->faker->words(rand(1, 5), true),
            'nominal'     => $this->faker->numberBetween(100_000, 2_999_999),
            'tgl_dipakai' => $this->faker->dateTimeBetween('2023-01-01', '2023-05-31'),
        ];
    }
}
