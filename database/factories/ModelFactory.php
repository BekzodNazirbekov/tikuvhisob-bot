<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class ModelFactory extends Factory
{
    protected $model = Model::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
        ];
    }
}
