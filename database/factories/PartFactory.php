<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Part>
 */
class PartFactory extends Factory
{
    protected $model = \App\Models\Part::class;

    public function definition(): array
    {
        return [
            'model_id' => Model::factory(),
            'name' => fake()->word(),
            'price' => fake()->randomFloat(2, 1, 10),
            'count' =>  fake()->numberBetween(10, 50),
        ];
    }
}
