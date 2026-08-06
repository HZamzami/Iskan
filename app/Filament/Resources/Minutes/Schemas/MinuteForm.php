<?php

namespace App\Filament\Resources\Minutes\Schemas;

use App\Enums\MinuteType;
use App\Enums\Site;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
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

class MinuteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المستند')
                    ->icon(Heroicon::DocumentText)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('type')
                                ->label('نوع المحضر')
                                ->options(MinuteType::class)
                                ->live()
                                ->required()
                                ->columnSpan(1),
                            CheckboxList::make('sites')
                                ->label('القسم / الموقع')
                                ->options(fn (Get $get): array => self::siteOptions($get))
                                ->visible(fn (Get $get): bool => self::isSiteScoped($get))
                                ->required(fn (Get $get): bool => self::isSiteScoped($get))
                                ->rule(fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    $allowed = array_keys(self::siteOptions($get));

                                    if (array_diff((array) $value, $allowed) !== []) {
                                        $fail('يجب اختيار مواقع متاحة لهذا النوع فقط.');
                                    }
                                })
                                ->bulkToggleable()
                                ->columns(4)
                                ->dehydrateStateUsing(fn (?array $state): ?array => filled($state) ? $state : null)
                                ->columnSpanFull(),
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
                            DatePicker::make('document_date')
                                ->label('تاريخ الملف')
                                ->required()
                                ->maxDate(now())
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('أطراف المحضر')
                    ->icon(Heroicon::UserGroup)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('parties')
                            ->label('الأطراف المشاركة')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('الملف والملاحظات')
                    ->icon(Heroicon::PaperClip)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('ملف المستند (PDF)')
                            ->disk('local')
                            ->directory('minutes-files')
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

    private static function selectedType(Get $get): ?MinuteType
    {
        $type = $get('type');

        if ($type instanceof MinuteType) {
            return $type;
        }

        return $type ? MinuteType::tryFrom($type) : null;
    }
}
