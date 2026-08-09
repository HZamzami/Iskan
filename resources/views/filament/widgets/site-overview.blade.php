<x-filament-widgets::widget>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-4">
        @foreach ($cards as $card)
            <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="h-1.5" style="background-color: {{ $card['accent'] }}"></div>

                <div class="space-y-3 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <x-filament::icon :icon="$card['site']->getIcon()" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $card['site']->name }}
                            </h3>
                        </div>

                        <x-filament::badge color="gray">
                            {{ $card['total'] }} وثيقة
                        </x-filament::badge>
                    </div>

                    <dl class="space-y-1 rounded-lg bg-gray-50 p-2.5 text-xs text-gray-500 dark:bg-white/5 dark:text-gray-400">
                        @if ($card['site']->contractor)
                            <div class="flex justify-between gap-2">
                                <dt>المقاول</dt>
                                <dd class="font-medium text-gray-700 dark:text-gray-200">{{ $card['site']->contractor }}</dd>
                            </div>
                        @endif
                        @if ($card['site']->consultant)
                            <div class="flex justify-between gap-2">
                                <dt>الاستشاري</dt>
                                <dd class="font-medium text-gray-700 dark:text-gray-200">{{ $card['site']->consultant }}</dd>
                            </div>
                        @endif
                        @if ($card['site']->asset_manager)
                            <div class="flex justify-between gap-2">
                                <dt>مدير الأصل</dt>
                                <dd class="font-medium text-gray-700 dark:text-gray-200">{{ $card['site']->asset_manager }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if (count($card['modules']))
                        <ul class="divide-y divide-gray-100 text-sm dark:divide-white/5">
                            @foreach ($card['modules'] as $row)
                                <li class="flex items-center justify-between gap-2 py-1.5">
                                    <a href="{{ $row['url'] }}" class="flex min-w-0 items-center gap-2 text-gray-700 hover:text-primary-600 dark:text-gray-200 dark:hover:text-primary-400">
                                        <x-filament::icon :icon="$row['module']->getIcon()" class="h-4 w-4 shrink-0 text-gray-400" />
                                        <span class="truncate">{{ $row['module']->getLabel() }}</span>
                                    </a>
                                    <span class="font-semibold tabular-nums text-gray-950 dark:text-white">{{ $row['count'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
</x-filament-widgets::widget>
