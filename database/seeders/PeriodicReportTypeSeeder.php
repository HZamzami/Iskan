<?php

namespace Database\Seeders;

use App\Models\PeriodicReportType;
use Illuminate\Database\Seeder;

class PeriodicReportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['slug' => 'monthly_report', 'name' => 'التقرير الشهري لمنظومة إسكان الحجاج بمشعر منى', 'color' => 'warning', 'site_scope' => 'all', 'sort_order' => 1],
            ['slug' => 'final_report', 'name' => 'التقرير الختامي لإدارة إسكان الحجاج بمشعر منى', 'color' => 'danger', 'site_scope' => 'all', 'sort_order' => 2],
            ['slug' => 'weekly_progress', 'name' => 'تقارير إنجاز الأعمال الأسبوعية', 'color' => 'info', 'site_scope' => 'all', 'sort_order' => 3],
            ['slug' => 'weekly_inventory_coding', 'name' => 'تقارير الحصر والترميز الأسبوعية', 'color' => 'success', 'site_scope' => 'all', 'sort_order' => 4],
            ['slug' => 'guidelines', 'name' => 'الأدلة الإسترشادية والإجرائية', 'color' => 'gray', 'site_scope' => 'all', 'sort_order' => 5],
        ];

        foreach ($rows as $row) {
            PeriodicReportType::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
