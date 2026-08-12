<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Filament\Exports\TaskExporter;
use App\Models\Role;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_template', false))
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير إلى Excel')
                    ->exporter(TaskExporter::class),
            ])
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان المهمة')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('requestTypeLabel')
                    ->label('نوع الطلب')
                    ->state(fn (Task $record): string => $record->requestTypeLabel())
                    ->badge()
                    ->color('gray'),
                TextColumn::make('assignee.name')
                    ->label('المكلَّف')
                    ->placeholder('—'),
                TextColumn::make('requester.name')
                    ->label('مقدّم الطلب')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('priority')
                    ->label('الأهمية')
                    ->badge(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('due_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y/m/d')
                    ->sortable()
                    ->color(fn (Task $record): ?string => $record->isOverdue() ? 'danger' : null),
                TextColumn::make('recurrence')
                    ->label('التكرار')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('priority')
                    ->label('الأهمية')
                    ->options(TaskPriority::class),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(TaskStatus::class),
                SelectFilter::make('recurrence')
                    ->label('التكرار')
                    ->options(TaskRecurrence::class),
                SelectFilter::make('assigned_role_id')
                    ->label('نوع الطلب')
                    ->options(fn (): array => Role::active()->ordered()->pluck('name', 'id')->all()),
                TernaryFilter::make('assigned_to_me')
                    ->label('المسندة إليّ')
                    ->placeholder('الجميع')
                    ->trueLabel('المسندة إليّ فقط')
                    ->falseLabel('غير ذلك')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('assigned_to', Filament::auth()->id()),
                        false: fn (Builder $query): Builder => $query->where('assigned_to', '!=', Filament::auth()->id()),
                    ),
                TernaryFilter::make('requested_by_me')
                    ->label('طلباتي')
                    ->placeholder('الجميع')
                    ->trueLabel('طلباتي فقط')
                    ->falseLabel('غير ذلك')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('requested_by', Filament::auth()->id()),
                        false: fn (Builder $query): Builder => $query->where('requested_by', '!=', Filament::auth()->id()),
                    ),
            ])
            ->recordActions([
                Action::make('markInProgress')
                    ->label('بدء التنفيذ')
                    ->icon(Heroicon::ArrowPath)
                    ->color('warning')
                    ->visible(fn (Task $record): bool => $record->status === TaskStatus::Pending && $record->canBeCompletedBy(Filament::auth()->user()))
                    ->action(fn (Task $record) => $record->update(['status' => TaskStatus::InProgress])),
                Action::make('markComplete')
                    ->label('إنهاء المهمة')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status !== TaskStatus::Completed && $record->canBeCompletedBy(Filament::auth()->user()))
                    ->action(fn (Task $record) => $record->update(['status' => TaskStatus::Completed, 'completed_at' => now()])),
                Action::make('stopRecurrence')
                    ->label('إيقاف التكرار')
                    ->icon(Heroicon::StopCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Task $record): bool => $record->parent_task_id !== null && $record->parent?->is_active)
                    ->action(function (Task $record): void {
                        $record->parent?->update(['is_active' => false]);

                        Notification::make()
                            ->success()
                            ->title('تم إيقاف تكرار المهمة')
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('تصدير المحدد إلى Excel')
                        ->exporter(TaskExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد مهام')
            ->emptyStateDescription('ابدأ بطلب أول مهمة من الأزرار أعلاه.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentList);
    }
}
