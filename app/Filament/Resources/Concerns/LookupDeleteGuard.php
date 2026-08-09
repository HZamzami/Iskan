<?php

namespace App\Filament\Resources\Concerns;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

/**
 * حارس حذف مشترك لجداول أنواع المستندات ومجموعات المتطلبات والمواقع: يمنع
 * حذف أي سجل لا يزال مستخدماً في سجلات أخرى، مطابقاً لنمط
 * UsersTable::isLastAdmin() للحذف الفردي والجماعي معاً.
 */
class LookupDeleteGuard
{
    public static function action(DeleteAction $action): DeleteAction
    {
        return $action->before(function (DeleteAction $action, Model $record): void {
            if (self::isInUse($record)) {
                self::notifyInUse();
                $action->cancel();
            }
        });
    }

    public static function bulkAction(DeleteBulkAction $action): DeleteBulkAction
    {
        return $action->before(function (DeleteBulkAction $action): void {
            $hasRecordInUse = $action->getSelectedRecords()
                ->contains(fn (Model $record): bool => self::isInUse($record));

            if ($hasRecordInUse) {
                self::notifyInUse();
                $action->cancel();
            }
        });
    }

    protected static function isInUse(Model $record): bool
    {
        return method_exists($record, 'isInUse') && $record->isInUse();
    }

    protected static function notifyInUse(): void
    {
        Notification::make()
            ->danger()
            ->title('لا يمكن إتمام العملية')
            ->body('لا يمكن حذف سجل لا يزال مستخدماً في مستندات أو سجلات أخرى. قم بإلغاء تفعيله بدلاً من ذلك.')
            ->send();
    }
}
