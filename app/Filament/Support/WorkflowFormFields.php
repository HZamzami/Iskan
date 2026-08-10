<?php

namespace App\Filament\Support;

use App\Models\EntityType;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;

/**
 * حقول اختيار "الجهة ثم الشخص" المشتركة عبر كل خطوة في سير الاعتماد (الإرسال
 * الأول عند الإنشاء، وكل ترحيل لاحق) — تُستخدم في نموذج الإنشاء (بأسماء
 * حقول افتراضية غير محفوظة مباشرة) وفي نافذة إجراء الترحيل (بأسماء الأعمدة
 * الحقيقية) على حد سواء.
 */
class WorkflowFormFields
{
    public static function categorySelect(string $name, string $label = 'الجهة'): Select
    {
        return Select::make($name)
            ->label($label)
            ->options(fn (): array => EntityType::active()->ordered()->pluck('name', 'id')->all())
            ->live()
            ->searchable()
            ->native(false);
    }

    public static function userSelect(string $name, string $categoryFieldName, string $label = 'الشخص'): Select
    {
        return Select::make($name)
            ->label($label)
            ->options(fn (Get $get, ?Model $record): array => self::eligibleUsers(
                $get($categoryFieldName),
                $record?->sites ?? (array) ($get('sites') ?? []),
            ))
            ->searchable()
            ->preload()
            ->native(false);
    }

    /**
     * @return array<int, string>
     */
    public static function eligibleUsers(int|string|null $categoryId, array $sites): array
    {
        if (blank($categoryId)) {
            return [];
        }

        $category = EntityType::find($categoryId);

        if ($category === null) {
            return [];
        }

        return User::query()
            ->ofCategory($category->slug)
            ->get()
            ->filter(fn (User $user): bool => collect($sites)->every(fn (string $slug): bool => $user->canAccessSite($slug)))
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * قسم "سير الاعتماد" في نموذج الإنشاء — يظهر فقط عندما يتطلب النوع
     * المختار سير اعتماد. الحقول هنا افتراضية (dehydrated(false)) ولا تُحفظ
     * مباشرة؛ صفحة الإنشاء تقرأها يدوياً بعد إنشاء السجل وتستدعي
     * WorkflowService::submit() (انظر SubmitsWorkflowOnCreate).
     */
    public static function submissionSection(Closure $isVisible): Section
    {
        return Section::make('سير الاعتماد')
            ->description('هذا النوع يتطلب مراجعة واعتماداً قبل اعتبار السجل نهائياً. اختر الجهة ثم الشخص الذي سيُرسَل إليه السجل أولاً.')
            ->visible($isVisible)
            ->columnSpanFull()
            ->schema([
                self::categorySelect('workflow_entity_type_id')
                    ->required($isVisible)
                    ->dehydrated(false),
                self::userSelect('workflow_assigned_to', 'workflow_entity_type_id')
                    ->required($isVisible)
                    ->dehydrated(false),
                Textarea::make('workflow_note')
                    ->label('ملاحظة (اختياري)')
                    ->rows(2)
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }
}
