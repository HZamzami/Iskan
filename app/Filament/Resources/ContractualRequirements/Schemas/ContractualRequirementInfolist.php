<?php

namespace App\Filament\Resources\ContractualRequirements\Schemas;

use App\Models\ContractualRequirement;
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
                            TextEntry::make('type')
                                ->label('نوع المتطلب')
                                ->badge()
                                ->columnSpan(1),
                            TextEntry::make('group')
                                ->label('المجموعة')
                                ->state(fn (ContractualRequirement $record): string => $record->type->group()->getLabel())
                                ->badge()
                                ->color(fn (ContractualRequirement $record): string => $record->type->group()->getColor())
                                ->columnSpan(1),
                            TextEntry::make('sites')
                                ->label('القسم / الموقع')
                                ->badge()
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
            ]);
    }
}
