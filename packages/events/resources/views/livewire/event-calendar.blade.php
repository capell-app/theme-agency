<section class="capell-event-calendar capell-events-calendar">
    <header>
        <button
            type="button"
            wire:click="previousMonth"
            aria-label="{{ __('capell-events::generic.previous_month') }}"
        >
            &larr;
        </button>

        <h2>{{ $monthDate->format('F Y') }}</h2>

        <button
            type="button"
            wire:click="nextMonth"
            aria-label="{{ __('capell-events::generic.next_month') }}"
        >
            &rarr;
        </button>
    </header>

    <div
        role="grid"
        aria-label="{{ __('capell-events::generic.event_calendar') }}"
    >
        @foreach ($weeks as $week)
            <div role="row">
                @foreach ($week->days as $day)
                    <section
                        role="gridcell"
                        aria-label="{{ $day->format('j F Y') }}"
                    >
                        <time datetime="{{ $day->toDateString() }}">
                            {{ $day->day }}
                        </time>

                        @foreach ($occurrences->filter(fn (EventOccurrenceViewData $occurrence): bool => $occurrence->startsAt?->isSameDay($day) ?? false) as $occurrence)
                            <article>
                                @if ($occurrence->url)
                                    <a href="{{ $occurrence->url }}">
                                        {{ $occurrence->title }}
                                    </a>
                                @else
                                    <span>{{ $occurrence->title }}</span>
                                @endif

                                <time
                                    datetime="{{ $occurrence->isoStartsAt }}"
                                >
                                    {{ $occurrence->displayStartsAt }}
                                    {{ $occurrence->eventTimezone }}
                                </time>

                                @if ($occurrence->viewerDisplayStartsAt && $occurrence->viewerTimezone !== $occurrence->eventTimezone)
                                    <time
                                        datetime="{{ $occurrence->isoStartsAt }}"
                                    >
                                        {{ __('capell-events::generic.your_time') }}:
                                        {{ $occurrence->viewerDisplayStartsAt }}
                                        {{ $occurrence->viewerTimezone }}
                                    </time>
                                @endif
                            </article>
                        @endforeach
                    </section>
                @endforeach
            </div>
        @endforeach
    </div>
</section>
