<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * إجراءا "معاينة" و"تنزيل" المشتركان للمرفق الوحيد لكل سجل (عمود file_path)،
 * يُستخدَمان بنفس الشكل في جدول كل وحدة (recordActions) وفي رأس صفحة العرض
 * (getHeaderActions) على حد سواء.
 */
class FileActions
{
    public static function preview(string $pathColumn = 'file_path'): Action
    {
        return Action::make('preview')
            ->label('معاينة')
            ->icon(Heroicon::Eye)
            ->color('gray')
            ->visible(fn (Model $record): bool => filled($record->{$pathColumn}))
            ->url(fn (Model $record): string => Storage::disk(config('filesystems.default'))->temporaryUrl(
                $record->{$pathColumn},
                now()->addMinutes(5),
            ))
            ->openUrlInNewTab();
    }

    public static function download(string $pathColumn = 'file_path', string $nameColumn = 'reference_number'): Action
    {
        return Action::make('download')
            ->label('تنزيل')
            ->icon(Heroicon::ArrowDownTray)
            ->color('gray')
            ->visible(fn (Model $record): bool => filled($record->{$pathColumn}))
            ->action(fn (Model $record) => Storage::disk(config('filesystems.default'))->download(
                $record->{$pathColumn},
                $record->{$nameColumn}.'.'.pathinfo($record->{$pathColumn}, PATHINFO_EXTENSION),
            ));
    }
}
