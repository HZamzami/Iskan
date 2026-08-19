<?php

namespace App\Filament\Widgets;

use App\Enums\PaletteColor;
use App\Filament\Widgets\Concerns\AppliesSiteScope;
use App\Models\FinancialFlow;
use App\Models\FinancialFlowType;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class FinancialFlowsChart extends ChartWidget
{
    use AppliesSiteScope;

    protected ?string $heading = 'التدفقات المالية الشهرية';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['default' => 1, 'xl' => 2];

    protected ?string $maxHeight = '280px';

    public ?string $filter = 'current';

    public static function canView(): bool
    {
        return Filament::auth()->user()?->can('viewAny', FinancialFlow::class) ?? false;
    }

    protected function getFilters(): ?array
    {
        return [
            'current' => 'السنة الحالية',
            'previous' => 'السنة الماضية',
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $year = $this->filter === 'previous' ? now()->subYear()->year : now()->year;

        $rows = $this->applySiteScope(FinancialFlow::query())
            ->whereBetween('period_month', ["{$year}-01-01", "{$year}-12-31"])
            ->get(['type', 'period_month', 'amount']);

        $byTypeMonth = [];

        foreach ($rows as $row) {
            $month = (int) $row->period_month->format('n');
            $byTypeMonth[$row->type][$month] = ($byTypeMonth[$row->type][$month] ?? 0) + (float) $row->amount;
        }

        $labels = collect(range(1, 12))
            ->map(fn (int $month): string => Carbon::create($year, $month, 1)->translatedFormat('F'))
            ->all();

        return [
            'labels' => $labels,
            'datasets' => FinancialFlowType::active()->ordered()->get()->map(fn (FinancialFlowType $type): array => [
                'label' => $type->short_label ?? $type->name,
                'data' => collect(range(1, 12))
                    ->map(fn (int $month): float => $byTypeMonth[$type->slug][$month] ?? 0.0)
                    ->all(),
                'backgroundColor' => (PaletteColor::tryFrom($type->color) ?? PaletteColor::Primary)->hex(),
            ])->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true, 'ticks' => ['maxRotation' => 0, 'autoSkipPadding' => 8]],
                'y' => ['stacked' => true, 'beginAtZero' => true],
            ],
        ];
    }
}
