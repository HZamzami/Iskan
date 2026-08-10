<?php

namespace App\Filament\Support;

use App\Models\Role;
use App\Models\User;
use App\Services\WorkflowService;
use Filament\Facades\Filament;

/**
 * تُستخدَم في صفحات الإنشاء (CreateRecord) للوحدات الست: تقرأ حقول "سير
 * الاعتماد" الافتراضية غير المحفوظة (workflow_role_id/
 * workflow_assigned_to/workflow_note من WorkflowFormFields::submissionSection())
 * بعد إنشاء السجل مباشرة، وتستدعي WorkflowService::submit() لبدء السلسلة.
 * لا تفعل شيئاً إن كان النوع المختار لا يتطلب سير اعتماد (الحقول غير موجودة
 * في $this->data عندها لأن القسم كان مخفياً).
 */
trait SubmitsWorkflowOnCreate
{
    protected function afterCreate(): void
    {
        $roleId = $this->data['workflow_role_id'] ?? null;
        $userId = $this->data['workflow_assigned_to'] ?? null;

        if (blank($roleId) || blank($userId)) {
            return;
        }

        app(WorkflowService::class)->submit(
            $this->record,
            Filament::auth()->user(),
            Role::findOrFail($roleId),
            User::findOrFail($userId),
            $this->data['workflow_note'] ?? null,
        );
    }
}
