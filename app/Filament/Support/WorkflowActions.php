<?php

namespace App\Filament\Support;

use App\Enums\WorkflowAction as WorkflowActionEnum;
use App\Enums\WorkflowStatus;
use App\Models\EntityType;
use App\Models\User;
use App\Services\WorkflowService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;

/**
 * الإجراءات الثلاثة المشتركة لسير الاعتماد (ترحيل/إعادة/اعتماد نهائي)،
 * تُستخدَم بنفس الشكل في جدول كل وحدة (recordActions) وفي رأس صفحة العرض
 * (getHeaderActions) على حد سواء. لا يوجد إجراء منفصل لكل جهة — الجهة
 * المستهدفة تُختار داخل نافذة "ترحيل" نفسها في كل مرة.
 */
class WorkflowActions
{
    /**
     * @return array<int, Action>
     */
    public static function forRecord(): array
    {
        return [
            self::forward(),
            self::return(),
            self::approve(),
        ];
    }

    public static function forward(): Action
    {
        $enum = WorkflowActionEnum::Forward;

        return Action::make('workflowForward')
            ->label($enum->getLabel())
            ->icon($enum->getIcon())
            ->color($enum->getColor())
            ->visible(fn (Model $record): bool => self::isPending($record) && $record->canBeActedOnBy(Filament::auth()->user()))
            ->schema([
                WorkflowFormFields::categorySelect('entity_type_id')->required(),
                WorkflowFormFields::userSelect('assigned_to', 'entity_type_id')->required(),
                Textarea::make('note')
                    ->label('ملاحظة (اختياري)')
                    ->rows(2),
            ])
            ->action(function (Model $record, array $data): void {
                app(WorkflowService::class)->forward(
                    $record,
                    Filament::auth()->user(),
                    EntityType::findOrFail($data['entity_type_id']),
                    User::findOrFail($data['assigned_to']),
                    $data['note'] ?? null,
                );
            })
            ->successNotificationTitle('تم ترحيل السجل بنجاح');
    }

    public static function return(): Action
    {
        $enum = WorkflowActionEnum::Return;

        return Action::make('workflowReturn')
            ->label($enum->getLabel())
            ->icon($enum->getIcon())
            ->color($enum->getColor())
            ->visible(fn (Model $record): bool => self::isPending($record)
                && $record->canBeActedOnBy(Filament::auth()->user())
                && $record->transitions()->exists())
            ->requiresConfirmation()
            ->schema([
                Textarea::make('note')
                    ->label('سبب الإعادة (اختياري)')
                    ->rows(2),
            ])
            ->action(function (Model $record, array $data): void {
                app(WorkflowService::class)->returnToPrevious(
                    $record,
                    Filament::auth()->user(),
                    $data['note'] ?? null,
                );
            })
            ->successNotificationTitle('تمت إعادة السجل');
    }

    public static function approve(): Action
    {
        $enum = WorkflowActionEnum::Approve;

        return Action::make('workflowApprove')
            ->label($enum->getLabel())
            ->icon($enum->getIcon())
            ->color($enum->getColor())
            ->visible(fn (Model $record): bool => self::isPending($record) && $record->canApprove(Filament::auth()->user()))
            ->requiresConfirmation()
            ->schema([
                Textarea::make('note')
                    ->label('ملاحظة الاعتماد (اختياري)')
                    ->rows(2),
            ])
            ->action(function (Model $record, array $data): void {
                app(WorkflowService::class)->approve(
                    $record,
                    Filament::auth()->user(),
                    $data['note'] ?? null,
                );
            })
            ->successNotificationTitle('تم الاعتماد النهائي');
    }

    private static function isPending(Model $record): bool
    {
        return $record->workflow_status === WorkflowStatus::Pending;
    }
}
