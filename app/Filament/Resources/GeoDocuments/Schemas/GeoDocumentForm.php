<?php

namespace App\Filament\Resources\GeoDocuments\Schemas;

use App\Enums\GeoDocumentType;
use App\Enums\Site;
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
                                ->options(GeoDocumentType::class)
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
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.google-earth.kml+xml',
                                'application/vnd.google-earth.kmz',
                                'application/zip',
                                'image/vnd.dwg',
                                'image/png',
                                'image/jpeg',
                            ])
                            ->maxSize(51200)
                            ->helperText('PDF، KML/KMZ، ZIP، DWG أو صور')
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
            ->mapWithKeys(fn (Site $site): array => [$site->value => $site->getLabel()])
            ->all();
    }

    private static function selectedType(Get $get): ?GeoDocumentType
    {
        $type = $get('type');

        if ($type instanceof GeoDocumentType) {
            return $type;
        }

        return $type ? GeoDocumentType::tryFrom($type) : null;
    }
}
