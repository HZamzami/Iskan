<?php

namespace App\Filament\Resources\FinancialFlows\Schemas;

use App\Filament\Support\WorkflowInfolist;
use App\Models\FinancialFlow;
use App\Models\Location;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FinancialFlowInfolist
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
                                ->label('نوع التدفق')
                                ->badge()
                                ->color(fn (FinancialFlow $record): string => $record->documentType?->color ?? 'gray')
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

                Section::make('البيانات المالية')
                    ->icon(Heroicon::Banknotes)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('period_month')
                                ->label('الشهر المالي')
                                ->date('Y/m')
                                ->columnSpan(1),
                            TextEntry::make('amount')
                                ->label('المبلغ')
                                ->numeric(decimalPlaces: 2)
                                ->suffix(' ريال')
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

                WorkflowInfolist::timelineSection(),
            ]);
    }
}
