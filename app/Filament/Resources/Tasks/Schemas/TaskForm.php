<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Enums\Module;
use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class TaskForm
{
    public static function configure(Schema $schema): Schema
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
                            Select::make('assigned_role_id')
                                ->label('جهة الإسناد')
                                ->options(fn (): array => Role::active()->ordered()->pluck('name', 'id')->all())
                                ->required()
                                ->live()
                                ->native(false)
                                ->columnSpan(1),
                            Select::make('assigned_to')
                                ->label('المكلَّف')
                                ->options(fn (Get $get): array => User::query()
                                    ->whereHas('role', fn ($query) => $query->whereKey($get('assigned_role_id')))
                                    ->pluck('name', 'id')
                                    ->all())
                                ->disabled(fn (Get $get): bool => blank($get('assigned_role_id')))
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->required()
                                ->columnSpan(1),
                            DatePicker::make('due_date')
                                ->label('تاريخ الانتهاء')
                                ->required()
                                ->minDate(now())
                                ->columnSpan(1),
                            TimePicker::make('due_time')
                                ->label('وقت الانتهاء')
                                ->seconds(false)
                                ->columnSpan(1),
                            Select::make('priority')
                                ->label('الأولوية')
                                ->options(TaskPriority::class)
                                ->default(TaskPriority::Normal)
                                ->required()
                                ->native(false)
                                ->columnSpan(1),
                            Select::make('recurrence')
                                ->label('التكرار')
                                ->options(TaskRecurrence::class)
                                ->default(TaskRecurrence::Once)
                                ->required()
                                ->native(false)
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('الربط بالأرشيف')
                    ->icon(Heroicon::Link)
                    ->columnSpanFull()
                    ->schema([
                        MorphToSelect::make('linkable')
                            ->label('سجل مرتبط (اختياري)')
                            ->types(collect(Module::cases())
                                ->map(fn (Module $module): MorphToSelect\Type => MorphToSelect\Type::make($module->modelClass())
                                    ->label($module->getLabel())
                                    ->getOptionLabelFromRecordUsing(fn (Model $record): string => $module === Module::Correspondences
                                        ? "{$record->reference_number} — {$record->subject}"
                                        : "{$record->reference_number} — {$record->title}")
                                    ->searchColumns($module === Module::Correspondences
                                        ? ['reference_number', 'subject']
                                        : ['reference_number', 'title']))
                                ->all())
                            ->searchable()
                            ->live()
                            ->visible(fn (Get $get): bool => blank($get('requested_module')))
                            ->columnSpanFull(),
                        Select::make('requested_module')
                            ->label('أو اطلب إنشاء سجل جديد من نوع')
                            ->helperText('يظهر للمكلَّف زر "إنشاء السجل الآن" يفتح نموذج الإنشاء المناسب ويربط السجل بالمهمة تلقائياً بعد حفظه.')
                            ->options(Module::class)
                            ->live()
                            ->native(false)
                            ->visible(fn (Get $get): bool => blank($get('linkable_id')))
                            ->columnSpanFull(),
                    ]),

                Section::make('الإشعارات والمرفقات')
                    ->icon(Heroicon::PaperClip)
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('notify_by_email')
                            ->label('إرسال إشعار عبر البريد الإلكتروني')
                            ->helperText('عند التعطيل، يصل الإشعار داخل المنصة فقط.')
                            ->columnSpanFull(),
                        FileUpload::make('file_path')
                            ->label('المرفق')
                            ->disk('local')
                            ->directory('tasks')
                            ->downloadable()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
