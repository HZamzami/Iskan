<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Models\Task;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TaskInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المهمة')
                    ->icon(Heroicon::ClipboardDocumentList)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('title')
                                ->label('عنوان المهمة')
                                ->columnSpanFull(),
                            TextEntry::make('requestTypeLabel')
                                ->label('نوع الطلب')
                                ->state(fn (Task $record): string => $record->requestTypeLabel())
                                ->columnSpan(1),
                            TextEntry::make('assignee.name')
                                ->label('المكلَّف')
                                ->columnSpan(1),
                            TextEntry::make('requester.name')
                                ->label('مقدّم الطلب')
                                ->columnSpan(1),
                            TextEntry::make('due_date')
                                ->label('تاريخ الانتهاء')
                                ->date('Y/m/d')
                                ->columnSpan(1),
                            TextEntry::make('due_time')
                                ->label('وقت الانتهاء')
                                ->time('H:i')
                                ->placeholder('—')
                                ->columnSpan(1),
                            TextEntry::make('priority')
                                ->label('الأولوية')
                                ->badge()
                                ->columnSpan(1),
                            TextEntry::make('status')
                                ->label('الحالة')
                                ->badge()
                                ->columnSpan(1),
                            TextEntry::make('recurrence')
                                ->label('التكرار')
                                ->badge()
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('الربط بالأرشيف')
                    ->icon(Heroicon::Link)
                    ->columnSpanFull()
                    ->visible(fn (Task $record): bool => $record->linkedRecordLabel() !== null)
                    ->schema([
                        TextEntry::make('linkable')
                            ->hiddenLabel()
                            ->state(fn (Task $record): ?string => $record->linkedRecordLabel())
                            ->url(fn (Task $record): ?string => $record->linkedRecordUrl())
                            ->openUrlInNewTab()
                            ->columnSpanFull(),
                    ]),

                Section::make('ملاحظات')
                    ->icon(Heroicon::PencilSquare)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
