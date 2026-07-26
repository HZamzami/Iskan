<?php

namespace App\Filament\Widgets;

use App\Enums\CorrespondenceStatus;
use App\Enums\Module;
use App\Filament\Widgets\Concerns\AppliesSiteScope;
use App\Models\ContractDocument;
use App\Models\Correspondence;
use App\Models\FinancialFlow;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ArchiveOverviewStats extends StatsOverviewWidget
{
    use AppliesSiteScope;

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        foreach (Module::cases() as $module) {
            if ($user->accessLevelFor($module) !== null) {
                return true;
            }
        }

        return false;
    }

    protected function getStats(): array
    {
        $stats = [];
        $modules = $this->accessibleModules();
        $siteFilterActive = $this->filteredSite() !== null;

        if (in_array(Module::Correspondences, $modules, true)) {
            $daily = Correspondence::query()
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->get(['created_at'])
                ->countBy(fn (Correspondence $record): string => $record->created_at->toDateString());

            $sparkline = collect(range(6, 0))
                ->map(fn (int $daysAgo): float => (float) ($daily[now()->subDays($daysAgo)->toDateString()] ?? 0))
                ->all();

            $stats[] = Stat::make('معاملات قيد المعالجة', Correspondence::query()->where('status', CorrespondenceStatus::InProgress)->count())
                ->description($siteFilterActive
                    ? 'تنتظر الإنجاز (المعاملات غير مرتبطة بالمواقع)'
                    : 'معاملات تنتظر الإنجاز')
                ->descriptionIcon(Heroicon::Clock)
                ->color('warning')
                ->chart($sparkline)
                ->url(Module::Correspondences->resourceClass()::getUrl('index'));
        }

        if (in_array(Module::ContractDocuments, $modules, true)) {
            $active = $this->applySiteScope(ContractDocument::query())
                ->whereDate('start_date', '<=', today())
                ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
                ->count();

            $expiringSoon = $this->applySiteScope(ContractDocument::query())
                ->whereDate('end_date', '>=', today())
                ->whereDate('end_date', '<=', today()->addDays(90))
                ->count();

            $stats[] = Stat::make('العقود السارية', $active)
                ->description($expiringSoon > 0 ? "{$expiringSoon} تنتهي خلال 90 يوماً" : 'لا عقود تنتهي قريباً')
                ->descriptionIcon($expiringSoon > 0 ? Heroicon::ExclamationTriangle : Heroicon::CheckCircle)
                ->color($expiringSoon > 0 ? 'danger' : 'success')
                ->url(Module::ContractDocuments->resourceClass()::getUrl('index'));
        }

        if (in_array(Module::FinancialFlows, $modules, true)) {
            $recentSum = (float) $this->applySiteScope(FinancialFlow::query())
                ->whereDate('document_date', '>=', today()->subDays(30))
                ->sum('amount');

            $monthly = $this->applySiteScope(FinancialFlow::query())
                ->whereDate('period_month', '>=', now()->subMonths(5)->startOfMonth())
                ->get(['period_month', 'amount'])
                ->groupBy(fn (FinancialFlow $record): string => $record->period_month->format('Y-m'))
                ->map(fn ($records): float => (float) $records->sum('amount'));

            $sparkline = collect(range(5, 0))
                ->map(fn (int $monthsAgo): float => $monthly[now()->subMonths($monthsAgo)->format('Y-m')] ?? 0.0)
                ->all();

            $stats[] = Stat::make('مستخلصات آخر 30 يوماً', number_format($recentSum).' ريال')
                ->description('مجموع التدفقات المالية المسجلة')
                ->descriptionIcon(Heroicon::Banknotes)
                ->color('primary')
                ->chart($sparkline)
                ->url(Module::FinancialFlows->resourceClass()::getUrl('index'));
        }

        $monthTotal = 0;

        foreach ($modules as $module) {
            if (! $module->isSiteScoped() && $siteFilterActive) {
                continue;
            }

            $query = $module->modelClass()::query()->where('created_at', '>=', now()->startOfMonth());

            if ($module->isSiteScoped()) {
                $query = $this->applySiteScope($query);
            }

            $monthTotal += $query->count();
        }

        $stats[] = Stat::make('وثائق هذا الشهر', $monthTotal)
            ->description('إجمالي ما أُضيف للأرشيف هذا الشهر')
            ->descriptionIcon(Heroicon::DocumentPlus)
            ->color('info');

        return $stats;
    }
}
