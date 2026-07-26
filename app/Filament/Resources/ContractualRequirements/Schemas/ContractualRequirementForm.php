<?php

namespace App\Filament\Resources\ContractualRequirements\Schemas;

use App\Enums\ContractualRequirementType;
use App\Enums\RequirementGroup;
use App\Enums\Site;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ContractualRequirementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المتطلب')
                    ->icon(Heroicon::ClipboardDocumentCheck)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('type')
                                ->label('نوع المتطلب')
                                ->options(self::groupedTypeOptions())
                                ->live()
                                ->required()
                                ->columnSpan(1),
                            Select::make('site')
                                ->label('القسم / الموقع')
                                ->options(fn (Get $get): array => self::siteOptions($get))
                                ->visible(fn (Get $get): bool => self::isSiteScoped($get))
                                ->required(fn (Get $get): bool => self::isSiteScoped($get))
                                ->columnSpan(1),
                            TextInput::make('reference_number')
                                ->label('الرقم المرجعي')
                                ->placeholder('يُولَّد تلقائياً عند الترك فارغاً')
                                ->unique(ignoreRecord: true)
                                ->maxLength(50)
                                ->columnSpan(1),
                            TextInput::make('title')
                                ->label('عنوان المستند')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            DatePicker::make('period')
                                ->label('الفترة (الشهر / السنة)')
                                ->displayFormat('Y/m')
                                ->columnSpan(1),
                            DatePicker::make('document_date')
                                ->label('تاريخ الملف')
                                ->required()
                                ->maxDate(now())
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('الملف والملاحظات')
                    ->icon(Heroicon::PaperClip)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('ملف المستند (PDF)')
                            ->disk('local')
                            ->directory('contractual-requirements')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20480)
                            ->downloadable()
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * خيارات نوع المتطلب مجمّعة حسب المجموعة.
     *
     * @return array<string, array<string, string>>
     */
    private static function groupedTypeOptions(): array
    {
        return collect(RequirementGroup::cases())
            ->mapWithKeys(fn (RequirementGroup $group): array => [
                $group->getLabel() => collect($group->types())
                    ->mapWithKeys(fn (ContractualRequirementType $type): array => [$type->value => $type->getLabel()])
                    ->all(),
            ])
            ->all();
    }

    private static function isSiteScoped(Get $get): bool
    {
        return self::selectedType($get)?->sites() !== null;
    }

    /**
     * @return array<string, string>
     */
    private static function siteOptions(Get $get): array
    {
        return collect(self::selectedType($get)?->sites() ?? [])
            ->filter(fn (Site $site): bool => Filament::auth()->user()->canAccessSite($site))
            ->mapWithKeys(fn (Site $site): array => [$site->value => $site->getLabel()])
            ->all();
    }

    private static function selectedType(Get $get): ?ContractualRequirementType
    {
        $type = $get('type');

        if ($type instanceof ContractualRequirementType) {
            return $type;
        }

        return $type ? ContractualRequirementType::tryFrom($type) : null;
    }
}
