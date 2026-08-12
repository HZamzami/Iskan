<?php

namespace App\Filament\Exports;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Spatie\Activitylog\Models\Activity;

class ActivityExporter extends Exporter
{
    protected static ?string $model = Activity::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')
                ->label('التاريخ والوقت'),
            ExportColumn::make('causer.name')
                ->label('المستخدم')
                ->state(fn (Activity $record): string => $record->causer?->name ?? 'النظام'),
            ExportColumn::make('event')
                ->label('العملية')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'created' => 'إنشاء',
                    'updated' => 'تعديل',
                    'deleted' => 'حذف',
                    default => $state ?? '—',
                }),
            ExportColumn::make('subject_type')
                ->label('الوحدة')
                ->formatStateUsing(fn (?string $state): string => ActivityResource::subjectTypeLabel($state)),
            ExportColumn::make('subject_id')
                ->label('رقم السجل'),
            ExportColumn::make('changes')
                ->label('التغييرات')
                ->state(function (Activity $record): string {
                    $old = $record->attribute_changes['old'] ?? [];
                    $new = $record->attribute_changes['attributes'] ?? [];

                    if (blank($old) && blank($new)) {
                        return '—';
                    }

                    return collect($new)
                        ->map(fn ($value, $key): string => "{$key}: ".($old[$key] ?? '—').' ← '.$value)
                        ->implode(' | ');
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "اكتمل تصدير سجل النشاط، وتم تصدير {$export->successful_rows} صف.";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= " فشل تصدير {$failedRowsCount} صف.";
        }

        return $body;
    }
}
