<?php

namespace Database\Seeders;

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

        // $workers = User::factory(3)->create();

        // $models = Model::factory(2)
        //     ->has(
        //         Part::factory()->count(2)
        //     )
        //     ->create();

        // foreach ($workers as $worker) {
        //     WorkEntry::factory(5)->create([
        //         'user_id' => $worker->id,
        //     ]);
        // }
    }
}
