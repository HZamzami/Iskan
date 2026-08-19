<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContractDocuments\ContractDocumentResource;
use App\Filament\Widgets\Concerns\AppliesSiteScope;
use App\Models\ContractDocument;
use App\Models\Location;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ExpiringContracts extends TableWidget
{
    use AppliesSiteScope;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = ['default' => 1, 'xl' => 2];

    public static function canView(): bool
    {
        return Filament::auth()->user()?->can('viewAny', ContractDocument::class) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('عقود تقارب نهايتها')
            ->query(
                $this->applySiteScope(ContractDocument::query())
                    ->whereDate('end_date', '>=', today())
                    ->whereDate('end_date', '<=', today()->addDays(120))
                    ->orderBy('end_date')
                    ->limit(5),
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('title')
                    ->label('العنوان')
                    ->limit(40),
                TextColumn::make('sites')
                    ->label('الموقع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Location::cached()->firstWhere('slug', $state)?->name ?? $state)
                    ->color(fn (string $state): string => Location::cached()->firstWhere('slug', $state)?->color ?? 'gray')
                    ->placeholder('عام'),
                TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y/m/d'),
                TextColumn::make('days_remaining')
                    ->label('الأيام المتبقية')
                    ->state(fn (ContractDocument $record): int => (int) today()->diffInDays($record->end_date))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state < 30 => 'danger',
                        $state < 90 => 'warning',
                        default => 'gray',
                    }),
            ])
            ->recordUrl(fn (ContractDocument $record): string => ContractDocumentResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('لا توجد عقود قاربت على الانتهاء')
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle);
    }
}
