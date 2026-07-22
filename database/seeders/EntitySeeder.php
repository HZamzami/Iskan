<?php

namespace Database\Seeders;

use App\Models\Entity;
use Illuminate\Database\Seeder;

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'وزارة الإسكان',
            'أمانة المنطقة',
            'البلدية',
            'وزارة المالية',
            'وزارة الموارد البشرية',
            'الهيئة العامة للعقار',
            'شركة الكهرباء',
            'شركة المياه الوطنية',
        ];

        foreach ($names as $name) {
            Entity::query()->firstOrCreate(['name' => $name]);
        }
    }
}
