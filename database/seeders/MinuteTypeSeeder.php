<?php

namespace Database\Seeders;

use App\Models\MinuteType;
use Illuminate\Database\Seeder;

class MinuteTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campSites = ['site_a', 'site_b', 'site_c'];

        $rows = [
            ['slug' => 'weekly_meeting', 'name' => 'محاضر الاجتماعات الأسبوعية', 'color' => 'info', 'site_scope' => 'all', 'sort_order' => 1],
            ['slug' => 'project_handover', 'name' => 'محاضر تسليم واستلام المشاريع', 'color' => 'success', 'site_scope' => 'none', 'sort_order' => 2],
            ['slug' => 'service_provider', 'name' => 'محاضر شركات تقديم الخدمة', 'color' => 'warning', 'site_scope' => 'none', 'sort_order' => 3],
            ['slug' => 'service_provider_re_receipt', 'name' => 'محاضر إعادة استلام من شركات تقديم الخدمة', 'color' => 'gray', 'site_scope' => 'none', 'sort_order' => 4],
            ['slug' => 'damages_extensions', 'name' => 'محاضر التلفيات والتمديدات', 'color' => 'danger', 'site_scope' => 'none', 'sort_order' => 5],
            ['slug' => 'asset_removal', 'name' => 'محاضر إزالة الأصول من المواقع من قبل شركات تقديم الخدمة', 'color' => 'primary', 'site_scope' => 'none', 'sort_order' => 6],
            ['slug' => 'asset_tagging', 'name' => 'محاضر تسليم علامات ترميز الأصول', 'color' => 'info', 'site_scope' => 'custom', 'sites' => $campSites, 'sort_order' => 7],
            ['slug' => 'spare_parts_handover', 'name' => 'محاضر تسليم واستلام قطع الغيار', 'color' => 'warning', 'site_scope' => 'custom', 'sites' => $campSites, 'sort_order' => 8],
            ['slug' => 'ac_sterilization_receipt', 'name' => 'محضر استلام أقراص تعقيم المكيفات', 'color' => 'success', 'site_scope' => 'none', 'sort_order' => 9],
        ];

        foreach ($rows as $row) {
            MinuteType::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
