<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Activities\ActivityResource;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Spatie\Activitylog\Models\Activity;

class RecentActivity extends TableWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();

        return $user?->isAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('آخر الأنشطة')
            ->query(Activity::query()->with('causer')->latest()->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')
                    ->label('الوقت')
                    ->since(),
                TextColumn::make('causer.name')
                    ->label('المستخدم')
                    ->placeholder('النظام'),
                TextColumn::make('event')
                    ->label('العملية')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => 'إنشاء',
                        'updated' => 'تعديل',
                        'deleted' => 'حذف',
                        default => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subject_type')
                    ->label('الوحدة')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => ActivityResource::subjectTypeLabel($state)),
                TextColumn::make('subject_id')
                    ->label('رقم السجل'),
            ])
            ->recordUrl(fn (Activity $record): string => ActivityResource::getUrl('view', ['record' => $record]));
    }
}
