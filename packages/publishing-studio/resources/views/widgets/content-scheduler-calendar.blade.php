<x-filament-widgets::widget class="capell-content-scheduler-calendar">
    <x-filament::section>
        <x-slot name="heading">
            {{ __('capell-publishing-studio::scheduler.calendar.heading') }}
        </x-slot>

        <div class="space-y-4">
            @forelse ($this->eventsByDate as $date => $events)
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

                    <div
                        class="space-y-2"
                        aria-live="polite"
                    >
                        @foreach ($events as $event)
                            @php
                                $eventLabel = implode(' ', array_filter([
                                    $dateLabel,
                                    $event->eventTypeLabel ?? $event->eventType,
                                    $event->state,
                                    $event->title,
                                    $event->startsAt->format('H:i'),
                                    $event->timezone,
                                ]));
                            @endphp

                            @if ($event->recordUrl !== null)
                                <a
                                    href="{{ $event->recordUrl }}"
                                    aria-label="{{ $eventLabel }}"
                                    class="focus:ring-primary-500 flex items-start justify-between gap-3 rounded-md border border-gray-100 px-3 py-2 text-sm transition hover:bg-gray-50 focus:ring-2 focus:outline-none dark:border-white/10 dark:hover:bg-white/5"
                                >
                                    <span>
                                        <span
                                            class="mb-1 inline-flex rounded px-1.5 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 dark:text-gray-200 dark:ring-white/10"
                                        >
                                            {{ $event->eventTypeLabel ?? $event->eventType }}
                                            @if ($event->state !== null)
                                                ·
                                                {{ $event->state }}
                                            @endif
                                        </span>
                                        <span
                                            class="font-medium text-gray-950 dark:text-white"
                                        >
                                            {{ $event->title }}
                                        </span>
                                        <span
                                            class="block text-gray-500 dark:text-gray-400"
                                        >
                                            {{ $event->description }}
                                        </span>
                                        @if ($event->failure !== null)
                                            <span
                                                class="text-danger-600 dark:text-danger-400 block"
                                            >
                                                {{ $event->failure }}
                                            </span>
                                        @endif
                                    </span>
                                    <span
                                        class="shrink-0 text-gray-500 dark:text-gray-400"
                                    >
                                        {{ $event->startsAt->format('H:i') }}
                                        <span class="sr-only">
                                            {{ $event->timezone }}
                                        </span>
                                    </span>
                                </a>
                            @else
                                <div
                                    role="group"
                                    aria-label="{{ $eventLabel }}"
                                    class="flex items-start justify-between gap-3 rounded-md border border-gray-100 px-3 py-2 text-sm dark:border-white/10"
                                >
                                    <span>
                                        <span
                                            class="mb-1 inline-flex rounded px-1.5 py-0.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 dark:text-gray-200 dark:ring-white/10"
                                        >
                                            {{ $event->eventTypeLabel ?? $event->eventType }}
                                            @if ($event->state !== null)
                                                ·
                                                {{ $event->state }}
                                            @endif
                                        </span>
                                        <span
                                            class="font-medium text-gray-950 dark:text-white"
                                        >
                                            {{ $event->title }}
                                        </span>
                                        <span
                                            class="block text-gray-500 dark:text-gray-400"
                                        >
                                            {{ $event->description }}
                                        </span>
                                        @if ($event->failure !== null)
                                            <span
                                                class="text-danger-600 dark:text-danger-400 block"
                                            >
                                                {{ $event->failure }}
                                            </span>
                                        @endif
                                    </span>
                                    <span
                                        class="shrink-0 text-gray-500 dark:text-gray-400"
                                    >
                                        {{ $event->startsAt->format('H:i') }}
                                        <span class="sr-only">
                                            {{ $event->timezone }}
                                        </span>
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @empty
                <div
                    class="rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400"
                >
                    {{ __('capell-publishing-studio::scheduler.calendar.empty') }}
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
