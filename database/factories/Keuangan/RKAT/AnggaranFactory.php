<?php

namespace Database\Factories\Keuangan\RKAT;

use Illuminate\Database\Eloquent\Factories\Factory;

class AnggaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama'      => $this->faker->words(rand(1, 5), true),
            'deskripsi' => $this->faker->paragraph(),
            'tahun'     => '2023',
        ];
    }
}
