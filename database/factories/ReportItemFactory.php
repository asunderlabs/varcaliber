<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReportItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'description' => $this->faker->text(150),
            'minutes' => random_int(1, 80) * 15,
            'hourly_rate' => 100,
            'fixed_amount' => null,
            'work_entry_id' => null,
        ];
    }
}
