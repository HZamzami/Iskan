<?php

namespace Database\Seeders;

use App\Enums\ContractDocumentType;
use App\Enums\ContractualRequirementType;
use App\Enums\CorrespondenceDirection;
use App\Enums\CorrespondenceStatus;
use App\Enums\FinancialFlowType;
use App\Enums\GeoDocumentType;
use App\Enums\MinuteType;
use App\Enums\PeriodicReportType;
use App\Enums\Site;
use App\Models\ContractDocument;
use App\Models\ContractualRequirement;
use App\Models\Correspondence;
use App\Models\Entity;
use App\Models\FinancialFlow;
use App\Models\GeoDocument;
use App\Models\Minute;
use App\Models\PeriodicReport;
use Illuminate\Database\Seeder;

/**
 * بيانات تجريبية لجميع وحدات الأرشيف؛ آمنة للتشغيل في بيئة الإنتاج
 * (لا تعتمد على حزم التطوير) وقابلة لإعادة التشغيل دون تكرار.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(EntitySeeder::class);

        $this->seedCorrespondences();
        $this->seedContractDocuments();
        $this->seedMinutes();
        $this->seedFinancialFlows();
        $this->seedContractualRequirements();
        $this->seedPeriodicReports();
        $this->seedGeoDocuments();
    }

    private function seedCorrespondences(): void
    {
        $entity = Entity::query()->where('name', 'وزارة الإسكان')->first()
            ?? Entity::query()->firstOrCreate(['name' => 'وزارة الإسكان']);

        $rows = [
            [
                'subject' => 'طلب تزويد ببيانات المخيمات المطورة',
                'direction' => CorrespondenceDirection::Incoming,
                'status' => CorrespondenceStatus::InProgress,
                'sender' => 'وزارة الإسكان',
                'recipient' => 'إدارة إسكان الحجاج',
                'document_date' => '2026-06-15',
            ],
            [
                'subject' => 'إفادة بجاهزية مواقع الإسكان لموسم الحج',
                'direction' => CorrespondenceDirection::Outgoing,
                'status' => CorrespondenceStatus::Completed,
                'sender' => 'إدارة إسكان الحجاج',
                'recipient' => 'أمانة المنطقة',
                'document_date' => '2026-05-20',
            ],
            [
                'subject' => 'تعميم بشأن اشتراطات السلامة في الخيام',
                'direction' => CorrespondenceDirection::Incoming,
                'status' => CorrespondenceStatus::New,
                'sender' => 'الدفاع المدني',
                'recipient' => 'إدارة إسكان الحجاج',
                'document_date' => '2026-07-01',
            ],
        ];

        foreach ($rows as $row) {
            Correspondence::query()->firstOrCreate(
                ['subject' => $row['subject']],
                [...$row, 'entity_id' => $entity->id, 'file_path' => 'correspondence-files/placeholder.pdf'],
            );
        }
    }

    private function seedContractDocuments(): void
    {
        $rows = [
            [
                'title' => 'عقد الإشراف الإستشاري - إيهاف',
                'type' => ContractDocumentType::ConsultantContract,
                'site' => null,
                'contracting_party' => 'إيهاف',
            ],
            [
                'title' => 'عقد الصيانة والتشغيل - موقع (أ)',
                'type' => ContractDocumentType::OperationContract,
                'site' => Site::SiteA,
                'contracting_party' => 'عزام الشريف',
            ],
            [
                'title' => 'عقد الصيانة والتشغيل - أبراج كدانة الوادي',
                'type' => ContractDocumentType::OperationContract,
                'site' => Site::AbrajKudanah,
                'contracting_party' => 'شركة الراجحي',
            ],
        ];

        foreach ($rows as $row) {
            ContractDocument::query()->firstOrCreate(
                ['title' => $row['title']],
                [...$row, 'document_date' => '2026-01-15', 'file_path' => 'contract-documents/placeholder.pdf'],
            );
        }
    }

    private function seedMinutes(): void
    {
        $rows = [
            [
                'title' => 'محضر الاجتماع الأسبوعي - موقع (ب)',
                'type' => MinuteType::WeeklyMeeting,
                'site' => Site::SiteB,
                'parties' => 'إدارة إسكان الحجاج، عزام الشريف، إيهاف',
            ],
            [
                'title' => 'محضر استلام مشروع تطوير المخيمات',
                'type' => MinuteType::ProjectHandover,
                'site' => null,
                'parties' => 'إدارة إسكان الحجاج، المقاول المنفذ',
            ],
            [
                'title' => 'محضر ترميز الأصول - موقع (أ)',
                'type' => MinuteType::AssetTagging,
                'site' => Site::SiteA,
                'parties' => 'إدارة إسكان الحجاج، عزام الشريف',
            ],
        ];

        foreach ($rows as $row) {
            Minute::query()->firstOrCreate(
                ['title' => $row['title']],
                [...$row, 'document_date' => '2026-06-10', 'file_path' => 'minutes-files/placeholder.pdf'],
            );
        }
    }

    private function seedFinancialFlows(): void
    {
        $rows = [
            [
                'title' => 'مستخلص الإستشاري - شهر يونيو 2026',
                'type' => FinancialFlowType::Consultant,
                'site' => null,
                'amount' => 850000,
            ],
            [
                'title' => 'مستخلص الصيانة والتشغيل - موقع (أ) - يونيو 2026',
                'type' => FinancialFlowType::Operation,
                'site' => Site::SiteA,
                'amount' => 2400000,
            ],
            [
                'title' => 'مستخلص الصيانة والتشغيل - موقع (ج) - يونيو 2026',
                'type' => FinancialFlowType::Operation,
                'site' => Site::SiteC,
                'amount' => 1750000,
            ],
        ];

        foreach ($rows as $row) {
            FinancialFlow::query()->firstOrCreate(
                ['title' => $row['title']],
                [...$row, 'period_month' => '2026-06-01', 'document_date' => '2026-07-05', 'file_path' => 'financial-flows/placeholder.pdf'],
            );
        }
    }

    private function seedContractualRequirements(): void
    {
        $rows = [
            [
                'title' => 'حصر أعداد العمالة - موقع (أ) - يونيو 2026',
                'type' => ContractualRequirementType::LaborCount,
                'site' => Site::SiteA,
            ],
            [
                'title' => 'حصر المعدات - أبراج كدانة الوادي - يونيو 2026',
                'type' => ContractualRequirementType::EquipmentCount,
                'site' => Site::AbrajKudanah,
            ],
            [
                'title' => 'الهيكل التنظيمي لفريق التشغيل - موقع (ب)',
                'type' => ContractualRequirementType::OrgStructure,
                'site' => Site::SiteB,
            ],
        ];

        foreach ($rows as $row) {
            ContractualRequirement::query()->firstOrCreate(
                ['title' => $row['title']],
                [...$row, 'period' => '2026-06-01', 'document_date' => '2026-07-01', 'file_path' => 'contractual-requirements/placeholder.pdf'],
            );
        }
    }

    private function seedPeriodicReports(): void
    {
        $rows = [
            [
                'title' => 'التقرير الشهري - أبراج كدانة الوادي - يونيو 2026',
                'type' => PeriodicReportType::MonthlyReport,
                'site' => Site::AbrajKudanah,
            ],
            [
                'title' => 'تقرير إنجاز الأعمال الأسبوعي - موقع (أ)',
                'type' => PeriodicReportType::WeeklyProgress,
                'site' => Site::SiteA,
            ],
            [
                'title' => 'تقرير الحصر والترميز الأسبوعي - موقع (ج)',
                'type' => PeriodicReportType::WeeklyInventoryCoding,
                'site' => Site::SiteC,
            ],
        ];

        foreach ($rows as $row) {
            PeriodicReport::query()->firstOrCreate(
                ['title' => $row['title']],
                [...$row, 'period' => '2026-06-01', 'document_date' => '2026-07-03', 'file_path' => 'periodic-reports/placeholder.pdf'],
            );
        }
    }

    private function seedGeoDocuments(): void
    {
        $rows = [
            [
                'title' => 'خريطة GIS لمخيمات موقع (أ)',
                'type' => GeoDocumentType::Gis,
                'site' => Site::SiteA,
                'drawing_number' => 'GIS-2026-001',
            ],
            [
                'title' => 'المخطط التنفيذي (As-Built) - أبراج كدانة الوادي',
                'type' => GeoDocumentType::AsBuiltDrawing,
                'site' => Site::AbrajKudanah,
                'drawing_number' => 'DWG-2026-014',
            ],
        ];

        foreach ($rows as $row) {
            GeoDocument::query()->firstOrCreate(
                ['title' => $row['title']],
                [...$row, 'document_date' => '2026-05-25', 'file_path' => 'geo-documents/placeholder.pdf'],
            );
        }
    }
}
