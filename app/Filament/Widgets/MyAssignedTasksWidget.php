<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class MyAssignedTasksWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'المهام المسندة إليّ';

    public static function canView(): bool
    {
        return Task::query()
            ->instances()
            ->where('assigned_to', Filament::auth()->id())
            ->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('المهام المسندة إليّ')
            ->query(Task::query()
                ->instances()
                ->where('assigned_to', Filament::auth()->id())
                ->latest())
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان المهمة')
                    ->limit(40),
                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->badge(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('due_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y/m/d'),
            ])
            ->recordUrl(fn (Task $record): string => TaskResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('لا توجد مهام مسندة إليك');
    }
}
