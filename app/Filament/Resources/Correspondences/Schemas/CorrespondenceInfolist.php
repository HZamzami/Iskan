<?php

namespace App\Filament\Resources\Correspondences\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CorrespondenceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المعاملة')
                    ->icon(Heroicon::DocumentText)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('reference_number')
                                ->label('الرقم المرجعي')
                                ->copyable()
                                ->columnSpan(1),
                            TextEntry::make('direction')
                                ->label('نوع المعاملة')
                                ->badge()
                                ->columnSpan(1),
                            TextEntry::make('subject')
                                ->label('الموضوع')
                                ->columnSpanFull(),
                            TextEntry::make('status')
                                ->label('الحالة')
                                ->badge()
                                ->columnSpan(1),
                            TextEntry::make('document_date')
                                ->label('تاريخ الملف')
                                ->date('Y/m/d')
                                ->columnSpan(1),
                            TextEntry::make('created_at')
                                ->label('تاريخ الرفع')
                                ->dateTime('Y/m/d H:i')
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('الأطراف')
                    ->icon(Heroicon::UserGroup)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('sender')
                                ->label('من')
                                ->columnSpan(1),
                            TextEntry::make('recipient')
                                ->label('إلى')
                                ->columnSpan(1),
                            TextEntry::make('entity.name')
                                ->label('الجهة')
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
