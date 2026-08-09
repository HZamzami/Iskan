<?php

namespace App\Filament\Resources\Locations\Actions;

use App\Enums\Module;
use App\Enums\SiteScope;
use App\Models\Location;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

/**
 * إجراء يتيح للمدير، من شاشة واحدة، تحديد الأنواع (ذات نطاق "مواقع محددة")
 * التي يجب أن يظهر فيها الموقع الحالي كخيار — بدلاً من فتح كل نوع على حدة.
 * الأنواع ذات نطاق "كل المواقع" لا تحتاج هذا الإجراء لأنها تتضمن أي موقع
 * جديد تلقائياً.
 */
class ApplyLocationToTypesAction
{
    public static function make(): Action
    {
        return Action::make('applyToTypes')
            ->label('تطبيق على الأنواع')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->color('gray')
            ->modalHeading(fn (Location $record): string => "تطبيق موقع \"{$record->name}\" على الأنواع")
            ->modalDescription('حدد الأنواع التي يجب أن يظهر فيها هذا الموقع كخيار عند إنشاء سجل جديد. لا تظهر هنا سوى الأنواع ذات نطاق "مواقع محددة" — أنواع "كل المواقع" تتضمن هذا الموقع تلقائياً دون الحاجة لأي إجراء.')
            ->modalSubmitActionLabel('حفظ')
            ->schema(fn (Location $record): array => self::buildSchema())
            ->fillForm(fn (Location $record): array => self::currentState($record))
            ->action(function (Location $record, array $data): void {
                self::applyState($record, $data);

                Notification::make()
                    ->success()
                    ->title('تم تحديث ربط الموقع بالأنواع')
                    ->send();
            })
            ->visible(fn (): bool => self::customScopedModules()
                ->contains(fn (Module $module): bool => self::customTypesFor($module)->isNotEmpty()));
    }

    /**
     * @return Collection<int, Module>
     */
    private static function customScopedModules(): Collection
    {
        return collect(Module::cases())
            ->filter(fn (Module $module): bool => $module->isSiteScoped() && $module->typeModelClass() !== null);
    }

    /**
     * @return Collection<string, string>
     */
    private static function customTypesFor(Module $module): Collection
    {
        $typeClass = $module->typeModelClass();

        return $typeClass::query()
            ->where('site_scope', SiteScope::Custom->value)
            ->ordered()
            ->pluck('name', 'slug');
    }

    /**
     * @return array<int, Section>
     */
    private static function buildSchema(): array
    {
        return self::customScopedModules()
            ->map(function (Module $module) {
                $types = self::customTypesFor($module);

                if ($types->isEmpty()) {
                    return null;
                }

                return Section::make($module->getLabel())
                    ->schema([
                        CheckboxList::make("modules.{$module->value}")
                            ->hiddenLabel()
                            ->options($types)
                            ->bulkToggleable()
                            ->columns(2),
                    ]);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function currentState(Location $record): array
    {
        $state = [];

        foreach (self::customScopedModules() as $module) {
            $typeClass = $module->typeModelClass();

            $state['modules'][$module->value] = $typeClass::query()
                ->where('site_scope', SiteScope::Custom->value)
                ->whereJsonContains('sites', $record->slug)
                ->pluck('slug')
                ->all();
        }

        return $state;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function applyState(Location $record, array $data): void
    {
        foreach (self::customScopedModules() as $module) {
            $typeClass = $module->typeModelClass();

            /** @var array<int, string> $selected */
            $selected = $data['modules'][$module->value] ?? [];

            foreach ($typeClass::query()->where('site_scope', SiteScope::Custom->value)->get() as $type) {
                $sites = collect($type->sites ?? []);
                $shouldContain = in_array($type->slug, $selected, true);
                $contains = $sites->contains($record->slug);

                if ($shouldContain === $contains) {
                    continue;
                }

                $updatedSites = $shouldContain
                    ? $sites->push($record->slug)->unique()->values()->all()
                    : $sites->reject(fn (string $slug): bool => $slug === $record->slug)->values()->all();

                $type->update(['sites' => $updatedSites]);
            }
        }
    }
}
