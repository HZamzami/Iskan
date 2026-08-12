<?php

namespace App\Filament\Exports;

use App\Models\Task;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TaskExporter extends Exporter
{
    protected static ?string $model = Task::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('title')
                ->label('عنوان المهمة'),
            ExportColumn::make('requestType')
                ->label('نوع الطلب')
                ->state(fn (Task $record): string => $record->requestTypeLabel()),
            ExportColumn::make('assignee.name')
                ->label('المكلَّف'),
            ExportColumn::make('requester.name')
                ->label('مقدّم الطلب'),
            ExportColumn::make('priority')
                ->label('الأهمية')
                ->formatStateUsing(fn (Task $record): string => $record->priority->getLabel()),
            ExportColumn::make('status')
                ->label('الحالة')
                ->formatStateUsing(fn (Task $record): string => $record->status->getLabel()),
            ExportColumn::make('due_date')
                ->label('تاريخ الإنتهاء'),
            ExportColumn::make('recurrence')
                ->label('التكرار')
                ->formatStateUsing(fn (Task $record): string => $record->recurrence->getLabel()),
            ExportColumn::make('created_at')
                ->label('تاريخ الطلب'),
            ExportColumn::make('completed_at')
                ->label('تاريخ الإنجاز'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = "اكتمل تصدير المهام، وتم تصدير {$export->successful_rows} صف.";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= " فشل تصدير {$failedRowsCount} صف.";
        }

        return $body;
    }
}
