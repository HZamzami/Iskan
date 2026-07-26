<?php

namespace App\Filament\Widgets;

use App\Enums\FinancialFlowType;
use App\Filament\Widgets\Concerns\AppliesSiteScope;
use App\Models\FinancialFlow;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class FinancialFlowsChart extends ChartWidget
{
    use AppliesSiteScope;

    protected ?string $heading = 'التدفقات المالية الشهرية';

    protected static ?int $sort = 4;

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
            $byTypeMonth[$row->type->value][$month] = ($byTypeMonth[$row->type->value][$month] ?? 0) + (float) $row->amount;
        }

        $labels = collect(range(1, 12))
            ->map(fn (int $month): string => Carbon::create($year, $month, 1)->translatedFormat('F'))
            ->all();

        $palette = [
            'info' => '#3b82f6',
            'warning' => '#f59e0b',
            'gray' => '#6b7280',
        ];

        return [
            'labels' => $labels,
            'datasets' => collect(FinancialFlowType::cases())->map(fn (FinancialFlowType $type): array => [
                'label' => $type->getLabel(),
                'data' => collect(range(1, 12))
                    ->map(fn (int $month): float => $byTypeMonth[$type->value][$month] ?? 0.0)
                    ->all(),
                'backgroundColor' => $palette[$type->getColor()] ?? '#f59e0b',
            ])->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true, 'reverse' => true],
                'y' => ['stacked' => true, 'beginAtZero' => true],
            ],
            'plugins' => [
                'legend' => ['rtl' => true, 'textDirection' => 'rtl'],
                'tooltip' => ['rtl' => true, 'textDirection' => 'rtl'],
            ],
        ];
    }
}
