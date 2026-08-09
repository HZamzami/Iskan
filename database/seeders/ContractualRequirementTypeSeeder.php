<?php

namespace Database\Seeders;

use App\Models\ContractualRequirementType;
use App\Models\RequirementGroup;
use Illuminate\Database\Seeder;

class ContractualRequirementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupIds = RequirementGroup::query()->pluck('id', 'slug');
        $campSites = ['site_a', 'site_b', 'site_c'];

        $rows = [
            // قوائم الحصر الشهرية
            ['slug' => 'labor_count', 'name' => 'حصر العمالة الشهري', 'group' => 'monthly_counts', 'site_scope' => 'all', 'sort_order' => 1],
            ['slug' => 'equipment_count', 'name' => 'حصر المعدات الشهري', 'group' => 'monthly_counts', 'site_scope' => 'all', 'sort_order' => 2],
            ['slug' => 'spare_parts_count', 'name' => 'حصر قطع الغيار الشهري', 'group' => 'monthly_counts', 'site_scope' => 'all', 'sort_order' => 3],
            ['slug' => 'tools_count', 'name' => 'حصر الأدوات الشهري', 'group' => 'monthly_counts', 'site_scope' => 'all', 'sort_order' => 4],
            ['slug' => 'components_count', 'name' => 'حصر المكونات', 'group' => 'monthly_counts', 'site_scope' => 'all', 'sort_order' => 5],

            // وثائق التشغيل
            ['slug' => 'org_structure', 'name' => 'الهيكل التنظيمي للاستشاري والمقاول', 'group' => 'operation_docs', 'site_scope' => 'all', 'sort_order' => 6],
            ['slug' => 'sop', 'name' => 'إجراءات التشغيل الموحد (SOP)', 'group' => 'operation_docs', 'site_scope' => 'all', 'accepted_extensions' => ['pdf', 'doc', 'docx'], 'sort_order' => 7],
            ['slug' => 'completion_certificates', 'name' => 'شهادات إنجاز الأعمال', 'group' => 'operation_docs', 'site_scope' => 'all', 'sort_order' => 8],
            ['slug' => 'master_plan', 'name' => 'الجداول الزمنية (Master Plan)', 'group' => 'operation_docs', 'site_scope' => 'all', 'accepted_extensions' => ['pdf', 'xls', 'xlsx'], 'sort_order' => 9],
            ['slug' => 'job_plan', 'name' => 'بنود وخطوات عمل الصيانات (Job Plan)', 'group' => 'operation_docs', 'site_scope' => 'all', 'accepted_extensions' => ['pdf', 'xls', 'xlsx'], 'sort_order' => 10],

            // الخطط الإدارية
            ['slug' => 'quality_plan', 'name' => 'خطة إدارة الجودة', 'group' => 'management_plans', 'site_scope' => 'custom', 'sites' => $campSites, 'sort_order' => 11],
            ['slug' => 'risk_plan', 'name' => 'خطة إدارة المخاطر', 'group' => 'management_plans', 'site_scope' => 'custom', 'sites' => $campSites, 'sort_order' => 12],
            ['slug' => 'hse_plan', 'name' => 'خطة إدارة السلامة والصحة المهنية', 'group' => 'management_plans', 'site_scope' => 'custom', 'sites' => $campSites, 'sort_order' => 13],
            ['slug' => 'stakeholder_plan', 'name' => 'خطة إدارة أصحاب المصلحة (المعنيين)', 'group' => 'management_plans', 'site_scope' => 'custom', 'sites' => $campSites, 'sort_order' => 14],
            ['slug' => 'cash_flow_plan', 'name' => 'خطة التدفقات النقدية', 'group' => 'management_plans', 'site_scope' => 'custom', 'sites' => $campSites, 'sort_order' => 15],
            ['slug' => 'elemental_cost_plan', 'name' => 'خطط التكاليف المرحلية والنوعية (Elemental Cost Plan)', 'group' => 'management_plans', 'site_scope' => 'custom', 'sites' => $campSites, 'sort_order' => 16],
            ['slug' => 'logistics_plan', 'name' => 'خطة إدارة اللوجستيات', 'group' => 'management_plans', 'site_scope' => 'custom', 'sites' => $campSites, 'sort_order' => 17],
            ['slug' => 'interface_plan', 'name' => 'خطة إدارة التداخلات (Interface Management Plan)', 'group' => 'management_plans', 'site_scope' => 'custom', 'sites' => $campSites, 'sort_order' => 18],
        ];

        foreach ($rows as $row) {
            $groupSlug = $row['group'];
            unset($row['group']);
            $row['requirement_group_id'] = $groupIds[$groupSlug];

            ContractualRequirementType::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
