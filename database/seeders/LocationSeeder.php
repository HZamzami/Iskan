<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            [
                'slug' => 'site_a',
                'name' => 'موقع (أ)',
                'color' => 'info',
                'icon' => 'map-pin',
                'contractor' => 'عزام الشريف',
                'consultant' => 'إيهاف & عبد الله العيدروس',
                'asset_manager' => 'راشد الرفاعي',
                'sort_order' => 1,
            ],
            [
                'slug' => 'site_b',
                'name' => 'موقع (ب)',
                'color' => 'success',
                'icon' => 'map-pin',
                'contractor' => 'عزام الشريف',
                'consultant' => 'إيهاف & عبد الله العيدروس',
                'asset_manager' => 'عبد الله الأمير',
                'sort_order' => 2,
            ],
            [
                'slug' => 'site_c',
                'name' => 'موقع (ج)',
                'color' => 'warning',
                'icon' => 'map-pin',
                'contractor' => 'الظاهري',
                'consultant' => 'إيهاف & عبد الله العيدروس',
                'asset_manager' => 'أحمد الصبحي',
                'sort_order' => 3,
            ],
            [
                'slug' => 'abraj_kudanah',
                'name' => 'أبراج كدانة الوادي',
                'color' => 'danger',
                'icon' => 'building-office-2',
                'contractor' => 'شركة الراجحي',
                'consultant' => 'إيهاف & عبد الله العيدروس',
                'asset_manager' => 'م. أحمد',
                'sort_order' => 4,
            ],
        ];

        foreach ($rows as $row) {
            Location::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
