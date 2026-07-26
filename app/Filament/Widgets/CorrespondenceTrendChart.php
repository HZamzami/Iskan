<?php

namespace App\Filament\Widgets;

use App\Enums\CorrespondenceDirection;
use App\Filament\Widgets\Concerns\AppliesSiteScope;
use App\Models\Correspondence;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class CorrespondenceTrendChart extends ChartWidget
{
    use AppliesSiteScope;

    protected ?string $heading = 'حركة المعاملات (٦ أشهر)';

    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return Filament::auth()->user()?->can('viewAny', Correspondence::class) ?? false;
    }

    public function getDescription(): ?string
    {
        return $this->filteredSite() !== null
            ? 'المعاملات غير مرتبطة بالمواقع — لا يتأثر هذا الرسم بفلتر الموقع'
            : null;
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo)->startOfMonth());

        $records = Correspondence::query()
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['direction', 'created_at']);

        $buckets = [];

        foreach ($records as $record) {
            $key = $record->created_at->format('Y-m');
            $buckets[$record->direction->value][$key] = ($buckets[$record->direction->value][$key] ?? 0) + 1;
        }

        $series = fn (CorrespondenceDirection $direction): array => $months
            ->map(fn ($month): int => $buckets[$direction->value][$month->format('Y-m')] ?? 0)
            ->all();

        return [
            'labels' => $months->map(fn ($month): string => $month->translatedFormat('F Y'))->all(),
            'datasets' => [
                [
                    'label' => 'وارد',
                    'data' => $series(CorrespondenceDirection::Incoming),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f6',
                    'fill' => false,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'صادر',
                    'data' => $series(CorrespondenceDirection::Outgoing),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => '#f59e0b',
                    'fill' => false,
                    'tension' => 0.3,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['reverse' => true],
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
            'plugins' => [
                'legend' => ['rtl' => true, 'textDirection' => 'rtl'],
                'tooltip' => ['rtl' => true, 'textDirection' => 'rtl'],
            ],
        ];
    }
}
