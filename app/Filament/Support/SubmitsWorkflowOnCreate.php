<?php

namespace App\Filament\Support;

use App\Models\EntityType;
use App\Models\User;
use App\Services\WorkflowService;
use Filament\Facades\Filament;

/**
 * تُستخدَم في صفحات الإنشاء (CreateRecord) للوحدات الست: تقرأ حقول "سير
 * الاعتماد" الافتراضية غير المحفوظة (workflow_entity_type_id/
 * workflow_assigned_to/workflow_note من WorkflowFormFields::submissionSection())
 * بعد إنشاء السجل مباشرة، وتستدعي WorkflowService::submit() لبدء السلسلة.
 * لا تفعل شيئاً إن كان النوع المختار لا يتطلب سير اعتماد (الحقول غير موجودة
 * في $this->data عندها لأن القسم كان مخفياً).
 */
trait SubmitsWorkflowOnCreate
{
    protected function afterCreate(): void
    {
        $entityTypeId = $this->data['workflow_entity_type_id'] ?? null;
        $userId = $this->data['workflow_assigned_to'] ?? null;

        if (blank($entityTypeId) || blank($userId)) {
            return;
        }

        app(WorkflowService::class)->submit(
            $this->record,
            Filament::auth()->user(),
            EntityType::findOrFail($entityTypeId),
            User::findOrFail($userId),
            $this->data['workflow_note'] ?? null,
        );
    }
}
