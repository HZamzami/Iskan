<?php

namespace App\Filament\Resources\ContractDocuments\Schemas;

use App\Models\ContractDocument;
use App\Models\Location;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ContractDocumentInfolist
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
                            TextEntry::make('reference_number')
                                ->label('الرقم المرجعي')
                                ->copyable()
                                ->columnSpan(1),
                            TextEntry::make('documentType.name')
                                ->label('نوع العقد')
                                ->badge()
                                ->color(fn (ContractDocument $record): string => $record->documentType?->color ?? 'gray')
                                ->columnSpan(1),
                            TextEntry::make('title')
                                ->label('عنوان المستند')
                                ->columnSpanFull(),
                            TextEntry::make('sites')
                                ->label('القسم / الموقع')
                                ->badge()
                                ->formatStateUsing(fn (string $state): string => Location::cached()->firstWhere('slug', $state)?->name ?? $state)
                                ->color(fn (string $state): string => Location::cached()->firstWhere('slug', $state)?->color ?? 'gray')
                                ->placeholder('غير مرتبط بموقع')
                                ->columnSpan(1),
                            TextEntry::make('document_date')
                                ->label('تاريخ الملف')
                                ->date('Y/m/d')
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('بيانات التعاقد')
                    ->icon(Heroicon::ClipboardDocumentCheck)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('contract_number')
                                ->label('رقم العقد')
                                ->placeholder('—')
                                ->columnSpan(1),
                            TextEntry::make('contracting_party')
                                ->label('الطرف المتعاقد')
                                ->placeholder('—')
                                ->columnSpan(1),
                            TextEntry::make('start_date')
                                ->label('بداية العقد')
                                ->date('Y/m/d')
                                ->placeholder('—')
                                ->columnSpan(1),
                            TextEntry::make('end_date')
                                ->label('نهاية العقد')
                                ->date('Y/m/d')
                                ->placeholder('—')
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('ملاحظات')
                    ->icon(Heroicon::PencilSquare)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('notes')
                            ->hiddenLabel()
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
