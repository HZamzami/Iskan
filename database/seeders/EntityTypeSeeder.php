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
        $rows = [
            ['slug' => 'contractor', 'name' => 'مقاول'],
            ['slug' => 'consultant', 'name' => 'استشاري'],
            ['slug' => 'owner', 'name' => 'مالك'],
        ];

        foreach ($rows as $row) {
            EntityType::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
