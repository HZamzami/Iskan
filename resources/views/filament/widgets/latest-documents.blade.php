<x-filament-widgets::widget>
    <x-filament::section heading="أحدث الإضافات">
        <ul class="divide-y divide-gray-100 dark:divide-white/5">
            @forelse ($items as $item)
                <li class="flex items-center justify-between gap-3 py-2.5">
                    <div class="flex min-w-0 items-center gap-3">
                        <x-filament::icon :icon="$item['module']->getIcon()" class="h-5 w-5 shrink-0 text-gray-400" />

                        <div class="min-w-0">
                            <a href="{{ $item['url'] }}" class="block truncate text-sm font-medium text-gray-950 hover:text-primary-600 dark:text-white dark:hover:text-primary-400">
                                {{ $item['title'] }}
                            </a>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $item['module']->getLabel() }} · {{ $item['created_at']->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    @if ($item['site'])
                        <x-filament::badge :color="$item['site']->getColor()">
                            {{ $item['site']->getLabel() }}
                        </x-filament::badge>
                    @endif
                </li>
            @empty
                <li class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">لا توجد وثائق بعد</li>
            @endforelse
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
