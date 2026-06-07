@php
    $sectionHeading = $heading ?? ($section->heading ?? null);
    $events = $section->items ?? [];
@endphp

<section
    class="theme-section theme-section-events nonprofit-bg-dark-gradient px-6 py-16 text-white"
>
    @if ($sectionHeading)
        <div class="mx-auto max-w-5xl">
            <p
                class="nonprofit-text-accent text-xs font-black tracking-[0.2em]"
            >
                {{ __('capell-theme-nonprofit::generic.events_label') }}
            </p>
            <h2 class="mt-3 text-4xl font-black tracking-tight">
                {{ $sectionHeading }}
            </h2>
            <p class="nonprofit-text-on-dark-muted mt-4 max-w-2xl">
                {{ $eventsAvailable ?? false ? __('capell-theme-nonprofit::generic.events_connected') : __('capell-theme-nonprofit::generic.events_static') }}
            </p>
        </div>

        @if ($events !== [])
            <div class="mx-auto mt-10 grid max-w-5xl gap-4 md:grid-cols-3">
                @foreach ($events as $event)
                    <article class="border border-white/15 bg-white/10 p-5">
                        <p
                            class="nonprofit-text-accent text-xs font-black tracking-[0.16em] uppercase"
                        >
                            {{ $event['date'] ?? $event['startsAt'] ?? $event['starts_at'] ?? __('capell-theme-nonprofit::generic.event_date_fallback') }}
                        </p>
                        <h3 class="mt-3 text-xl font-black text-white">
                            {{ $event['title'] ?? __('capell-theme-nonprofit::generic.events_label') }}
                        </h3>
                        <p
                            class="nonprofit-text-on-dark-muted mt-3 text-sm leading-6"
                        >
                            {{ $event['summary'] ?? $event['description'] ?? __('capell-theme-nonprofit::generic.events_connected') }}
                        </p>

                        <div
                            class="mt-5 flex flex-wrap gap-2 text-xs font-bold"
                        >
                            @foreach ([$event['location'] ?? null, $event['type'] ?? null] as $eventMeta)
                                @if ($eventMeta)
                                    <span
                                        class="border border-white/20 px-3 py-1 text-white"
                                    >
                                        {{ $eventMeta }}
                                    </span>
                                @endif
                            @endforeach
                        </div>

                        @if (($event['url'] ?? null) && ($event['label'] ?? null))
                            <a
                                href="{{ $event['url'] }}"
                                class="nonprofit-bg-accent nonprofit-text-on-accent mt-5 inline-flex px-4 py-2 text-sm font-black"
                            >
                                {{ $event['label'] }}
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    @endif
</section>
