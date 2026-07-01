@php
    $items = $section->items !== [] ? $section->items : [];
@endphp

<section class="liquid-glass-section px-5 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="max-w-3xl">
            <p class="liquid-glass-eyebrow">
                {{ $section->eyebrow ?: __('capell-theme-liquid-glass::generic.features_eyebrow') }}
            </p>
            <h2
                class="mt-4 text-4xl leading-tight font-black text-balance sm:text-5xl"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary)
                <p
                    class="mt-5 text-lg leading-8 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_72%,transparent)]"
                >
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-5 lg:grid-cols-3">
            @foreach ($items as $item)
                <article class="liquid-glass-panel overflow-hidden">
                    @if (! empty($item['image']))
                        <img
                            src="{{ $item['image'] }}"
                            alt="{{ $item['imageAlt'] ?? '' }}"
                            width="720"
                            height="520"
                            loading="lazy"
                            decoding="async"
                            class="aspect-[3/2] w-full object-cover"
                        />
                    @endif

                    <div class="p-6">
                        <p
                            class="text-sm font-bold tracking-wide text-[var(--theme-primary)] uppercase"
                        >
                            {{ $item['discipline'] ?? '' }}
                        </p>
                        <h3 class="mt-3 text-2xl leading-tight font-black">
                            {{ $item['title'] ?? '' }}
                        </h3>
                        <p
                            class="mt-4 text-sm leading-7 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_70%,transparent)]"
                        >
                            {{ $item['summary'] ?? '' }}
                        </p>
                        @if (! empty($item['metric']))
                            <p
                                class="mt-5 text-3xl font-black text-[var(--theme-primary)]"
                            >
                                {{ $item['metric'] }}
                                <span
                                    class="block text-xs font-bold tracking-wide text-[color-mix(in_srgb,var(--liquid-glass-foreground)_56%,transparent)] uppercase"
                                >
                                    {{ $item['metricLabel'] ?? '' }}
                                </span>
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
