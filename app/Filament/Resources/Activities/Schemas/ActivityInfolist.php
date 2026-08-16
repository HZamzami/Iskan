<?php

namespace App\Filament\Resources\Activities\Schemas;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Spatie\Activitylog\Models\Activity;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات النشاط')
                    ->icon(Heroicon::ClipboardDocumentList)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('created_at')
                                ->label('التاريخ والوقت')
                                ->dateTime('Y/m/d H:i:s')
                                ->columnSpan(1),
                            TextEntry::make('causer.name')
                                ->label('المستخدم')
                                ->placeholder('النظام')
                                ->columnSpan(1),
                            TextEntry::make('event')
                                ->label('العملية')
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => match ($state) {
                                    'created' => 'إنشاء',
                                    'updated' => 'تعديل',
                                    'deleted' => 'حذف',
                                    default => $state ?? '—',
                                })
                                ->color(fn (?string $state): string => match ($state) {
                                    'created' => 'success',
                                    'updated' => 'warning',
                                    'deleted' => 'danger',
                                    default => 'gray',
                                })
                                ->columnSpan(1),
                            TextEntry::make('subject_type')
                                ->label('الوحدة')
                                ->badge()
                                ->color('info')
                                ->formatStateUsing(fn (?string $state): string => ActivityResource::subjectTypeLabel($state))
                                ->columnSpan(1),
                            TextEntry::make('subject_id')
                                ->label('رقم السجل')
                                ->formatStateUsing(fn (Activity $record): string => ActivityResource::subjectLabel($record->subject_type, $record->subject_id))
                                ->url(fn (Activity $record): ?string => ActivityResource::subjectUrl($record->subject_type, $record->subject_id))
                                ->openUrlInNewTab()
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('القيم القديمة')
                    ->icon(Heroicon::ArrowUturnRight)
                    ->columnSpanFull()
                    ->visible(fn (Activity $record): bool => filled($record->attribute_changes['old'] ?? null))
                    ->schema([
                        KeyValueEntry::make('attribute_changes.old')
                            ->hiddenLabel()
                            ->keyLabel('الحقل')
                            ->valueLabel('القيمة'),
                    ]),

                Section::make('القيم الجديدة')
                    ->icon(Heroicon::ArrowRight)
                    ->columnSpanFull()
                    ->visible(fn (Activity $record): bool => filled($record->attribute_changes['attributes'] ?? null))
                    ->schema([
                        KeyValueEntry::make('attribute_changes.attributes')
                            ->hiddenLabel()
                            ->keyLabel('الحقل')
                            ->valueLabel('القيمة'),
                    ]),
            ]);
    }
}
