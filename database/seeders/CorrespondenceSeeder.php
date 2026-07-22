<?php

namespace Database\Seeders;

use App\Models\Correspondence;
use App\Models\Entity;
use Illuminate\Database\Seeder;

class CorrespondenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Correspondence::query()->exists()) {
            return;
        }

        Correspondence::factory()
            ->count(30)
            ->recycle(Entity::all())
            ->create();
    }
}
