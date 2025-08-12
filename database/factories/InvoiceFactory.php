<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'number' => 1,
            'billing_start' => now(),
            'billing_end' => now()->addDays(14),
            'issue_at' => now(),
            'approved_at' => now(),
            'delivered_at' => now(),
            'due_at' => now(),
            'paid' => false,
            'client_info' => [
                'contact_name' => 'Example contact name',
                'business_name' => 'Example business',
                'address_line_1' => '123 Main St',
                'address_line_2' => 'Suite 1',
                'city' => 'Dallas',
                'state' => 'TX',
                'zip_code' => '75214'
            ],
            'items' => [],
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'organization_id' => 1,
        ];
    }
}
