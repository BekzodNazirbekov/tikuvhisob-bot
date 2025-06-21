<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ModelFactory extends Factory
{
    protected $model = \App\Models\Model::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
        ];
    }
}
