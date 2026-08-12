<?php

namespace App\Filament\Widgets;

use App\Enums\Module;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;

class MyAssignedRecordsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'السجلات المسندة إليّ';

    public function table(Table $table): Table
    {
        return $table
            ->heading('السجلات المسندة إليّ')
            ->records(fn (): Collection => $this->assignedRecords())
            ->columns([
                TextColumn::make('title')
                    ->label('العنوان')
                    ->limit(40),
                TextColumn::make('module')
                    ->label('الوحدة')
                    ->badge()
                    ->color('info'),
                TextColumn::make('status')
                    ->label('حالة الاعتماد')
                    ->badge()
                    ->color(fn (array $record): ?string => $record['statusColor'] ?? null),
            ])
            ->recordUrl(fn (array $record): string => $record['url'])
            ->emptyStateHeading('لا توجد سجلات مسندة إليك');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function assignedRecords(): Collection
    {
        $userId = Filament::auth()->id();

        return collect(Module::cases())
            ->filter(fn (Module $module): bool => $module->isSiteScoped())
            ->flatMap(function (Module $module) use ($userId): Collection {
                $modelClass = $module->modelClass();

                return $modelClass::query()
                    ->where('assigned_to', $userId)
                    ->get()
                    ->map(fn ($record): array => [
                        'id' => "{$module->value}-{$record->getKey()}",
                        'title' => $record->title,
                        'module' => $module->getLabel(),
                        'status' => $record->workflow_status?->getLabel() ?? '—',
                        'statusColor' => $record->workflow_status?->getColor(),
                        'created_at' => $record->created_at,
                        'url' => $module->resourceClass()::getUrl('view', ['record' => $record->getKey()]),
                    ]);
            })
            ->sortByDesc('created_at')
            ->values();
    }
}
