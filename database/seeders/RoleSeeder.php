<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['slug' => 'contractor', 'name' => 'مقاول'],
            ['slug' => 'consultant', 'name' => 'استشاري'],
            ['slug' => 'asset_manager', 'name' => 'مدير الأصل'],
        ];

        foreach ($rows as $row) {
            Role::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
