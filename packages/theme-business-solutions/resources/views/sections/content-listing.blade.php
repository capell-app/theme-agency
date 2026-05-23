<section>
    <div class="business-theme-container">
        <div
            class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"
        >
            <div>
                <p
                    class="mb-3 text-sm font-bold uppercase tracking-[0.16em] text-[var(--theme-primary)]"
                >
                    {{ $profile['modern'] ? __('capell-theme-business-solutions::generic.modern_system') : __('capell-theme-business-solutions::generic.business_system') }}
                </p>
                <h2 class="text-3xl font-bold leading-tight sm:text-4xl">
                    {{ $section->heading }}
                </h2>
            </div>
            @if ($section->summary)
                <p class="max-w-xl text-base leading-7 text-slate-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div
            @class([
                'grid gap-4',
                'lg:grid-cols-[1.2fr_0.8fr_0.8fr]' => in_array($profile['layout'], ['search-hero', 'booking-hero', 'story-impact'], true),
                'sm:grid-cols-2 lg:grid-cols-4' => ! in_array($profile['layout'], ['search-hero', 'booking-hero', 'story-impact'], true),
            ])
        >
            @foreach ($section->items as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    @class([
                        'business-theme-card group block overflow-hidden p-5 transition hover:-translate-y-1',
                        'lg:row-span-2' => $loop->first && in_array($profile['layout'], ['search-hero', 'booking-hero', 'story-impact'], true),
                    ])
                >
                    @if (! empty($item['image']))
                        <img
                            src="{{ $item['image'] }}"
                            alt=""
                            class="mb-4 aspect-[5/3] w-full rounded-[var(--theme-radius-value)] object-cover"
                        />
                    @endif

                    <p
                        class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--theme-primary)]"
                    >
                        {{ $item['type'] ?? $item['meta'][0] ?? $profile['industry'] }}
                    </p>
                    <h3
                        class="mt-3 text-xl font-bold group-hover:text-[var(--theme-primary)]"
                    >
                        {{ $item['title'] }}
                    </h3>
                    @if (! empty($item['summary']))
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $item['summary'] }}
                        </p>
                    @endif

                    @if ($profile['modern'])
                        <span
                            class="mt-5 inline-flex h-9 w-9 items-center justify-center rounded-full bg-[var(--theme-primary)] text-white transition group-hover:translate-x-1"
                        >
                            &rarr;
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>
