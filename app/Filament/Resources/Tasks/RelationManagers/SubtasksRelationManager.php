<?php

namespace App\Filament\Resources\Tasks\RelationManagers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskNotifier;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubtasksRelationManager extends RelationManager
{
    protected static string $relationship = 'subtasks';

    protected static ?string $title = 'المهام الفرعية';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('عنوان المهمة الفرعية')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('assigned_to')
                    ->label('المكلَّف')
                    ->options(fn (): array => User::query()->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                DatePicker::make('due_date')
                    ->label('تاريخ الانتهاء')
                    ->minDate(now()),
                Select::make('priority')
                    ->label('الأولوية')
                    ->options(TaskPriority::class)
                    ->default(TaskPriority::Normal)
                    ->required()
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان المهمة الفرعية')
                    ->searchable(),
                TextColumn::make('assignee.name')
                    ->label('المكلَّف')
                    ->placeholder('—'),
                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->badge(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('due_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y/m/d')
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة مهمة فرعية')
                    ->after(fn (Task $record) => app(TaskNotifier::class)->notifyAssignment($record)),
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد مهام فرعية')
            ->emptyStateDescription('قسّم هذه المهمة إلى خطوات أصغر.');
    }
}
