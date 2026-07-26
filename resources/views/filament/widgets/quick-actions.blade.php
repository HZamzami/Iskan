<x-filament-widgets::widget>
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">إجراءات سريعة:</span>

        @foreach ($actions as $action)
            <x-filament::button
                tag="a"
                :href="$action['url']"
                :icon="\Filament\Support\Icons\Heroicon::Plus"
                size="sm"
                color="primary"
                outlined
            >
                {{ $action['label'] }}
            </x-filament::button>
        @endforeach
    </div>
</x-filament-widgets::widget>
