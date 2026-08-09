<?php

namespace Database\Seeders;

use App\Models\RequirementGroup;
use Illuminate\Database\Seeder;

class RequirementGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['slug' => 'monthly_counts', 'name' => 'قوائم الحصر الشهرية', 'color' => 'warning', 'sort_order' => 1],
            ['slug' => 'operation_docs', 'name' => 'وثائق التشغيل', 'color' => 'info', 'sort_order' => 2],
            ['slug' => 'management_plans', 'name' => 'الخطط الإدارية', 'color' => 'success', 'sort_order' => 3],
        ];

        foreach ($rows as $row) {
            RequirementGroup::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
