<?php

namespace App\Filament\Resources\Concerns;

use App\Enums\PaletteColor;
use App\Enums\SiteScope;
use App\Models\Location;
use App\Support\FileTypes;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * نموذج مشترك لأنواع المستندات الستة: يبني نفس الحقول (الاسم، اللون،
 * نطاق المواقع، الامتدادات المقبولة) لأي من نماذج النوع، مع حقل المجموعة
 * الإضافي فقط لِـ ContractualRequirementType.
 */
class DocumentTypeForm
{
    /**
     * @param  class-string  $modelClass
     */
    public static function configure(Schema $schema, string $modelClass): Schema
    {
        $supportsGroups = $modelClass::supportsGroups();

        return $schema
            ->components([
                Section::make('بيانات النوع')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('الاسم')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('slug')
                                ->label('المعرّف')
                                ->disabled()
                                ->dehydrated(false)
                                ->visible(fn (string $operation): bool => $operation === 'edit')
                                ->helperText('يُنشأ تلقائياً من الاسم عند الإنشاء، ولا يمكن تغييره لاحقاً.')
                                ->columnSpan(1),
                            TextInput::make('short_label')
                                ->label('اسم مختصر (اختياري)')
                                ->maxLength(100)
                                ->helperText('يُستخدم في الرسوم البيانية بدلاً من الاسم الكامل عند الحاجة.')
                                ->columnSpan(1),
                            Select::make('color')
                                ->label('اللون')
                                ->options(PaletteColor::class)
                                ->required(! $supportsGroups)
                                ->default($supportsGroups ? null : 'gray')
                                ->placeholder($supportsGroups ? 'استخدام لون المجموعة' : null)
                                ->columnSpan(1),
                            ...($supportsGroups ? [
                                Select::make('requirement_group_id')
                                    ->label('المجموعة')
                                    ->relationship('requirementGroup', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('اسم المجموعة')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique('requirement_groups', 'name'),
                                    ])
                                    ->columnSpan(1),
                            ] : []),
                            Textarea::make('description')
                                ->label('وصف مختصر (اختياري)')
                                ->rows(2)
                                ->helperText('يظهر كتلميح تحت حقل النوع عند إنشاء مستند.')
                                ->columnSpanFull(),
                            Toggle::make('is_active')
                                ->label('نشط')
                                ->default(true)
                                ->helperText('الأنواع غير النشطة تختفي من نموذج الإنشاء لكنها تبقى ظاهرة في السجلات القديمة.')
                                ->inline(false)
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('نطاق المواقع')
                    ->columnSpanFull()
                    ->schema([
                        Radio::make('site_scope')
                            ->hiddenLabel()
                            ->options(SiteScope::class)
                            ->descriptions([
                                SiteScope::None->value => SiteScope::None->description(),
                                SiteScope::All->value => SiteScope::All->description(),
                                SiteScope::Custom->value => SiteScope::Custom->description(),
                            ])
                            ->default(SiteScope::All->value)
                            ->live()
                            ->required(),
                        CheckboxList::make('sites')
                            ->label('المواقع')
                            ->options(fn (): array => Location::active()->ordered()->pluck('name', 'slug')->all())
                            ->visible(fn (Get $get): bool => self::isCustomScope($get))
                            ->required(fn (Get $get): bool => self::isCustomScope($get))
                            ->bulkToggleable()
                            ->columns(2),
                    ]),

                Section::make('الملفات')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TagsInput::make('accepted_extensions')
                                ->label('امتدادات الملفات المسموحة')
                                ->suggestions(FileTypes::suggestions())
                                ->placeholder('اتركه فارغاً للسماح بـ PDF فقط')
                                ->helperText('اكتب الامتداد بدون نقطة، مثل: pdf')
                                ->splitKeys([',', ' ', 'Tab', 'Enter'])
                                ->dehydrateStateUsing(fn (?array $state): ?array => filled($state)
                                    ? collect($state)->map(fn (string $ext): string => strtolower(ltrim(trim($ext), '.')))->unique()->values()->all()
                                    : null)
                                ->columnSpan(1),
                            TextInput::make('max_file_size')
                                ->label('الحد الأقصى لحجم الملف (كيلوبايت)')
                                ->numeric()
                                ->minValue(1)
                                ->placeholder('20480 (الافتراضي)')
                                ->columnSpan(1),
                        ]),
                    ]),
            ]);
    }

    /**
     * `Radio::options(SiteScope::class)` makes Filament resolve this field's
     * state as a real `SiteScope` case (not its raw string) when read via
     * `Get`, so both forms must be accepted here.
     */
    private static function isCustomScope(Get $get): bool
    {
        $value = $get('site_scope');

        return $value instanceof SiteScope
            ? $value === SiteScope::Custom
            : $value === SiteScope::Custom->value;
    }
}
