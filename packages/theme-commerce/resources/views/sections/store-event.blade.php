@php
    $events = $section->events ?? $section->items ?? [];
@endphp

<section
    class="theme-section theme-section-store-event bg-[var(--retail-surface)]"
>
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[var(--retail-primary)] uppercase"
                >
                    {{ __('capell-theme-commerce::generic.store_event_label') }}
                </p>
                <h2
                    class="mt-3 text-4xl font-black tracking-normal text-[var(--retail-ink)]"
                >
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p class="mt-4 text-base leading-7 text-stone-600">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-3">
                @forelse ($events as $event)
                    <article
                        class="rounded-2xl border border-[var(--retail-line)] bg-white p-5 text-[var(--retail-ink)]"
                    >
                        <p
                            class="text-xs font-black text-[var(--retail-primary)] uppercase"
                        >
                            {{ $event['date'] ?? $event['startsAt'] ?? __('capell-theme-commerce::generic.store_event_date_fallback') }}
                        </p>
                        <h3 class="mt-2 text-2xl font-black">
                            {{ $event['title'] ?? '' }}
                        </h3>
                        @if (! empty($event['location']))
                            <p class="mt-2 text-sm font-bold text-stone-500">
                                {{ $event['location'] }}
                            </p>
                        @endif

                        @if (! empty($event['summary']))
                            <p class="mt-4 text-sm leading-6 text-stone-600">
                                {{ $event['summary'] }}
                            </p>
                        @endif

                        @if (! empty($event['url']))
                            <a
                                href="{{ $event['url'] }}"
                                class="mt-5 inline-flex rounded-full bg-[var(--retail-primary)] px-4 py-2 text-sm font-black text-white"
                            >
                                {{ $event['ctaLabel'] ?? __('capell-theme-commerce::generic.store_event_cta') }}
                            </a>
                        @endif
                    </article>
                @empty
                    <div
                        class="rounded-2xl border border-dashed border-[var(--retail-line)] bg-white p-6"
                    >
                        <p
                            class="text-sm font-black text-[var(--retail-primary)] uppercase"
                        >
                            {{ __('capell-theme-commerce::generic.store_event_empty_title') }}
                        </p>
                        <p class="mt-3 text-sm leading-6 text-stone-600">
                            {{ __('capell-theme-commerce::generic.store_event_empty_summary') }}
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
