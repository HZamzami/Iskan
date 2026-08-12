<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class MyRequestedTasksWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'المهام التي طلبتها';

    public function table(Table $table): Table
    {
        return $table
            ->heading('المهام التي طلبتها')
            ->query(Task::query()
                ->instances()
                ->where('requested_by', Filament::auth()->id())
                ->latest())
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان المهمة')
                    ->limit(40),
                TextColumn::make('assignee.name')
                    ->label('المكلَّف')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('due_date')
                    ->label('تاريخ الإنتهاء')
                    ->date('Y/m/d'),
            ])
            ->recordUrl(fn (Task $record): string => TaskResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('لم تطلب أي مهمة بعد');
    }
}
