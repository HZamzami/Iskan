<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TaskForm
{
    public static function configure(Schema $schema, ?string $lockedRoleSlug = null): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المهمة')
                    ->icon(Heroicon::ClipboardDocumentList)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('عنوان المهمة')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Select::make('assigned_to')
                                ->label('الشخص المكلَّف')
                                ->options(fn (): array => User::query()
                                    ->ofCategory($lockedRoleSlug ?? '')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->required()
                                ->columnSpan(1),
                            DatePicker::make('due_date')
                                ->label('تاريخ الإنتهاء')
                                ->required()
                                ->minDate(now())
                                ->columnSpan(1),
                            Select::make('priority')
                                ->label('أهمية المهمة')
                                ->options(TaskPriority::class)
                                ->default(TaskPriority::Normal)
                                ->required()
                                ->native(false)
                                ->columnSpan(1),
                            Select::make('recurrence')
                                ->label('تكرار المهمة')
                                ->options(TaskRecurrence::class)
                                ->default(TaskRecurrence::Once)
                                ->required()
                                ->native(false)
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('التنبيهات والمرفقات')
                    ->icon(Heroicon::PaperClip)
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('notify_by_email')
                            ->label('إرسال تنبيه عبر البريد الإلكتروني')
                            ->helperText('عند التعطيل، يصل التنبيه داخل المنصة فقط.')
                            ->columnSpanFull(),
                        FileUpload::make('file_path')
                            ->label('المرفق')
                            ->disk('local')
                            ->directory('tasks')
                            ->downloadable()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('ملاحظة أو تفاصيل المهمة')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
