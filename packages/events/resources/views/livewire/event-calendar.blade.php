<section class="capell-event-calendar capell-events-calendar">
    <div class="flex items-center justify-between gap-4">
        <button
            type="button"
            wire:click="previousMonth"
        >
            {{ __('capell-events::generic.previous') }}
        </button>
        <h2>{{ $monthDate->format('F Y') }}</h2>
        <button
            type="button"
            wire:click="nextMonth"
        >
            {{ __('capell-events::generic.next') }}
        </button>
    </div>

    <div
        class="grid grid-cols-7 gap-2"
        role="grid"
        aria-label="{{ __('capell-events::generic.admin_calendar') }}: {{ $monthDate->format('F Y') }}"
    >
        <div
            class="contents"
            role="row"
        >
            @foreach ($weeks->first()?->days ?? [] as $day)
                <div
                    class="font-semibold"
                    role="columnheader"
                >
                    {{ $day->format('D') }}
                </div>
            @endforeach
        </div>

        @foreach ($weeks as $week)
            <div
                class="contents"
                role="row"
            >
                @foreach ($week->days as $day)
                    <div
                        class="min-h-24 border p-2"
                        role="gridcell"
                        aria-label="{{ $day->format('j F Y') }}"
                    >
                        <div>{{ $day->day }}</div>

                        @foreach (($occurrencesByDate[$day->toDateString()] ?? collect()) as $occurrence)
                            <a
                                href="{{ $occurrence->occurrenceUrl() }}"
                                class="block"
                            >
                                {{ $occurrence->event->translation?->title ?? $occurrence->event->name }}
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</section>
