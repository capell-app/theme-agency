<x-filament-widgets::widget class="capell-events-calendar-widget">
    <x-filament::section>
        <x-slot name="heading">
            {{ __('capell-events::generic.upcoming_occurrences') }}
        </x-slot>

        <div class="space-y-4">
            @forelse ($this->occurrencesByDate as $date => $occurrences)
                @php
                    $dateLabel = CarbonImmutable::parse($date)->isoFormat('dddd D MMMM YYYY');
                @endphp

                <div
                    class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900"
                >
                    <div
                        class="mb-3 text-sm font-semibold text-gray-950 dark:text-white"
                    >
                        {{ $dateLabel }}
                    </div>

                    <div class="space-y-2">
                        @foreach ($occurrences as $occurrence)
                            <div
                                class="flex items-start justify-between gap-3 rounded-md border border-gray-100 px-3 py-2 text-sm dark:border-white/10"
                            >
                                <span>
                                    <span
                                        class="font-medium text-gray-950 dark:text-white"
                                    >
                                        {{ $occurrence->effective_title ?? $occurrence->event->translation?->title ?? $occurrence->event->name }}
                                    </span>
                                    <span
                                        class="block text-gray-500 dark:text-gray-400"
                                    >
                                        {{ $occurrence->venue?->name ?? __('capell-events::generic.location_tbc') }}
                                    </span>
                                </span>
                                <span
                                    class="shrink-0 text-gray-500 dark:text-gray-400"
                                >
                                    {{ $occurrence->starts_at->timezone($occurrence->timezone)->format('H:i') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div
                    class="rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400"
                >
                    {{ __('capell-events::generic.no_upcoming_occurrences') }}
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
