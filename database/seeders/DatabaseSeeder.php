<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'مدير النظام',
            'email' => 'admin@iskan.test',
        ]);

        $this->call([
            EntitySeeder::class,
            CorrespondenceSeeder::class,
        ]);
    }
}
