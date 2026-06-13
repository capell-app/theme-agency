@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-restaurant::generic.events_heading'));
    $items = $section->items ?? ($items ?? []);
    $eventsAvailable ??= false;

    if (! is_array($items) || $items === []) {
        $items = [
            ['title' => __('capell-theme-restaurant::generic.event_one'), 'date' => __('capell-theme-restaurant::generic.event_one_date'), 'summary' => __('capell-theme-restaurant::generic.event_one_summary')],
            ['title' => __('capell-theme-restaurant::generic.event_two'), 'date' => __('capell-theme-restaurant::generic.event_two_date'), 'summary' => __('capell-theme-restaurant::generic.event_two_summary')],
        ];
    }
@endphp

<section class="theme-section restaurant-section-muted px-6 py-16">
    <div class="mx-auto max-w-6xl">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="restaurant-eyebrow">
                    {{ $eventsAvailable ? __('capell-theme-restaurant::generic.events_connected') : __('capell-theme-restaurant::generic.events_label') }}
                </p>
                <h2 class="mt-4 text-4xl leading-tight font-black">
                    {{ $heading }}
                </h2>
            </div>
        </div>
        <div class="mt-8 grid gap-4 lg:grid-cols-2">
            @foreach ($items as $event)
                <article class="restaurant-event-card">
                    <p class="restaurant-event-date">
                        {{ $event['date'] ?? '' }}
                    </p>
                    <div>
                        <h3 class="text-2xl font-black">
                            {{ $event['title'] ?? '' }}
                        </h3>
                        @if (($event['summary'] ?? null) !== null)
                            <p
                                class="mt-3 text-sm leading-6 text-[var(--restaurant-muted)]"
                            >
                                {{ $event['summary'] }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
