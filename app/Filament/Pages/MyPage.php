<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MyAssignedRecordsWidget;
use App\Filament\Widgets\MyAssignedTasksWidget;
use App\Filament\Widgets\MyCreatedRecordsWidget;
use App\Filament\Widgets\MyRequestedTasksWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MyPage extends Page
{
    protected static ?string $title = 'صفحتي';

    protected static ?string $slug = 'my-page';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?int $navigationSort = 100;

    /**
     * @return array<class-string>
     */
    protected function getFooterWidgets(): array
    {
        return [
            MyCreatedRecordsWidget::class,
            MyAssignedRecordsWidget::class,
            MyAssignedTasksWidget::class,
            MyRequestedTasksWidget::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }
}
