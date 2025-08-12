<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'description' => $this->faker->text(100),
            'starts_at' => null,
            'ends_at' => null,
            'report_item_id' => null,
            'organization_id' => null,
            'user_id' => null,
        ];
    }
}
