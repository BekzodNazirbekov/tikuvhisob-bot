<?php

namespace Database\Seeders;

use App\Models\Bot;
use App\Models\User;
use App\Models\Model;
use App\Models\Part;
use App\Models\WorkEntry;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'phone' => '998933054939',
            'role' => 'admin',
        ]);

        Bot::query()->create([
            'name' => 'Bot',
            'token' => "8026006539:AAEzPd1qKWBW8nUEdBjVf49yPEujDa-eHyg"
        ]);

        $models = Model::factory()->count(10)->create();

        $models->each(function ($model) {
            Part::factory()->count(10)->create([
                'model_id' => $model->id
            ]);
        });

    }
}
