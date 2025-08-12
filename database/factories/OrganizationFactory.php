<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $hourlyRates = [80, 90, 100, 120];
        $contactName = $this->faker->firstName() . ' ' . $this->faker->lastName();

        return [
            'name' => ucwords($this->faker->word()) . ' Company',
            'client_id' => '1' . random_int(100, 999),
            'billing_contact' => $contactName,
            'email' => str_replace(' ', '', $contactName) . '@example.com',
            'address_line_1' => random_int(1000, 5000) . ' ' . $this->faker->streetName(),
            'address_line_2' => $this->faker->secondaryAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'hourly_rate' => $hourlyRates[array_rand($hourlyRates)],
        ];
    }
}
