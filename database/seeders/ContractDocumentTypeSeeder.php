<?php

namespace Database\Seeders;

use App\Models\ContractDocumentType;
use Illuminate\Database\Seeder;

class ContractDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['slug' => 'consultant_contract', 'name' => 'عقود الصيانة والتشغيل الإستشاري', 'color' => 'info', 'site_scope' => 'none', 'sort_order' => 1],
            ['slug' => 'operation_contract', 'name' => 'عقود الصيانة والتشغيل', 'color' => 'warning', 'site_scope' => 'all', 'sort_order' => 2],
            ['slug' => 'internal_project_contract', 'name' => 'عقود المشاريع الداخلية', 'color' => 'gray', 'site_scope' => 'none', 'sort_order' => 3],
        ];

        foreach ($rows as $row) {
            ContractDocumentType::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
