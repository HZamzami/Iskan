<?php

namespace App\Filament\Support;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

/**
 * قسم "سجل الاعتماد" — يعرض سلسلة الانتقالات كاملة (من أرسل، لمن، بأي إجراء،
 * وبأي ملاحظة)، الأحدث أولاً. يظهر فقط للسجلات التي دخلت سير الاعتماد فعلاً
 * (transitions غير فارغة).
 */
class WorkflowInfolist
{
    public static function timelineSection(): Section
    {
        return Section::make('سجل الاعتماد')
            ->icon(Heroicon::ClipboardDocumentCheck)
            ->columnSpanFull()
            ->visible(fn ($record): bool => $record->transitions->isNotEmpty())
            ->schema([
                RepeatableEntry::make('transitions')
                    ->hiddenLabel()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('action')
                                ->label('الإجراء')
                                ->badge()
                                ->columnSpan(1),
                            TextEntry::make('actor.name')
                                ->label('بواسطة')
                                ->columnSpan(1),
                            TextEntry::make('assignedTo.name')
                                ->label('أُرسل إلى')
                                ->placeholder('—')
                                ->columnSpan(1),
                            TextEntry::make('created_at')
                                ->label('التاريخ')
                                ->dateTime('Y/m/d h:i A')
                                ->columnSpan(1),
                            TextEntry::make('note')
                                ->label('ملاحظة')
                                ->placeholder('—')
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->contained(false),
            ]);
    }
}
