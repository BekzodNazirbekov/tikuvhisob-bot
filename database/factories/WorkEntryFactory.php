<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkEntry>
 */
class WorkEntryFactory extends Factory
{
    protected $model = \App\Models\WorkEntry::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'part_id' => \App\Models\Part::factory(),
            'quantity' => fake()->numberBetween(1, 20),
            'date' => now(),
        ];
    }
}
