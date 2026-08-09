<?php

namespace Database\Seeders;

use App\Models\FinancialFlowType;
use Illuminate\Database\Seeder;

class FinancialFlowTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['slug' => 'consultant', 'name' => 'التدفقات المالية الخاصة بعقد الإستشاري', 'short_label' => 'عقد الإستشاري', 'color' => 'info', 'site_scope' => 'none', 'sort_order' => 1],
            ['slug' => 'operation', 'name' => 'التدفقات المالية الخاصة بعقد الصيانة والتشغيل', 'short_label' => 'الصيانة والتشغيل', 'color' => 'warning', 'site_scope' => 'all', 'sort_order' => 2],
            ['slug' => 'internal_projects', 'name' => 'التدفقات المالية الخاصة بعقود المشاريع الداخلية', 'short_label' => 'المشاريع الداخلية', 'color' => 'gray', 'site_scope' => 'none', 'sort_order' => 3],
        ];

        foreach ($rows as $row) {
            FinancialFlowType::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
