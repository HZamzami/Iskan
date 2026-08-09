<?php

namespace App\Filament\Resources\GeoDocuments\Schemas;

use App\Models\GeoDocumentType;
use App\Models\Location;
use App\Support\FileTypes;
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

class GeoDocumentForm
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
                                ->label('نوع الخريطة / الرسم')
                                ->options(fn (Get $get): array => GeoDocumentType::selectOptions($get('type')))
                                ->helperText(fn (Get $get): ?string => self::selectedType($get)?->description)
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
                            TextInput::make('drawing_number')
                                ->label('رقم المخطط')
                                ->maxLength(100)
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

                Section::make('الملف والملاحظات')
                    ->icon(Heroicon::PaperClip)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('ملف الخريطة / المخطط')
                            ->disk('local')
                            ->directory('geo-documents')
                            ->acceptedFileTypes(fn (Get $get): array => FileTypes::mimeTypesFor(self::acceptedExtensions($get)))
                            ->rule(fn (Get $get): string => 'extensions:'.implode(',', self::acceptedExtensions($get)))
                            ->maxSize(fn (Get $get): int => self::selectedType($get)?->maxFileSizeKb() ?? 51200)
                            ->helperText(fn (Get $get): string => 'الامتدادات المسموحة: '.implode('، ', self::acceptedExtensions($get)))
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
        return self::selectedType($get)?->isSiteScoped() ?? false;
    }

    /**
     * @return array<string, string>
     */
    private static function siteOptions(Get $get): array
    {
        return collect(self::selectedType($get)?->allowedLocations() ?? [])
            ->filter(fn (Location $location): bool => Filament::auth()->user()->canAccessSite($location))
            ->mapWithKeys(fn (Location $location): array => [$location->slug => $location->name])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function acceptedExtensions(Get $get): array
    {
        return self::selectedType($get)?->acceptedExtensions() ?? ['pdf'];
    }

    private static function selectedType(Get $get): ?GeoDocumentType
    {
        $type = $get('type');

        return filled($type) ? GeoDocumentType::cached()->firstWhere('slug', $type) : null;
    }
}
