<?php

namespace Database\Seeders;

use App\Models\EntityType;
use Illuminate\Database\Seeder;

class EntityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'مقاول',
            'استشاري',
            'مالك',
        ];

        foreach ($names as $name) {
            EntityType::query()->firstOrCreate(['name' => $name]);
        }
    }
}
