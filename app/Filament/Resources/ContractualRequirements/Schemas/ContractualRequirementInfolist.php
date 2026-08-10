<?php

namespace App\Filament\Resources\ContractualRequirements\Schemas;

use App\Filament\Support\WorkflowInfolist;
use App\Models\ContractualRequirement;
use App\Models\Location;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ContractualRequirementInfolist
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
                            TextEntry::make('reference_number')
                                ->label('الرقم المرجعي')
                                ->copyable()
                                ->columnSpan(1),
                            TextEntry::make('documentType.name')
                                ->label('نوع المتطلب')
                                ->badge()
                                ->color(fn (ContractualRequirement $record): string => $record->documentType?->color ?? 'gray')
                                ->columnSpan(1),
                            TextEntry::make('documentType.requirementGroup.name')
                                ->label('المجموعة')
                                ->badge()
                                ->color(fn (ContractualRequirement $record): ?string => $record->documentType?->requirementGroup?->color)
                                ->placeholder('—')
                                ->columnSpan(1),
                            TextEntry::make('sites')
                                ->label('القسم / الموقع')
                                ->badge()
                                ->formatStateUsing(fn (string $state): string => Location::cached()->firstWhere('slug', $state)?->name ?? $state)
                                ->color(fn (string $state): string => Location::cached()->firstWhere('slug', $state)?->color ?? 'gray')
                                ->placeholder('غير مرتبط بموقع')
                                ->columnSpan(1),
                            TextEntry::make('title')
                                ->label('عنوان المستند')
                                ->columnSpanFull(),
                            TextEntry::make('period')
                                ->label('الفترة')
                                ->date('Y/m')
                                ->placeholder('—')
                                ->columnSpan(1),
                            TextEntry::make('document_date')
                                ->label('تاريخ الملف')
                                ->date('Y/m/d')
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
