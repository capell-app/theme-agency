@php
    $variantLabel = match ($variant ?? null) {
        'faq' => __('capell-theme-liquid-glass::generic.variant_faq'),
        'media' => __('capell-theme-liquid-glass::generic.variant_media'),
        'metrics' => __('capell-theme-liquid-glass::generic.variant_metrics'),
        'people' => __('capell-theme-liquid-glass::generic.variant_people'),
        default => __('capell-theme-liquid-glass::generic.variant_editorial'),
    };
@endphp

<section class="liquid-glass-section px-5 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="liquid-glass-panel p-6 sm:p-8">
            <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                <div>
                    <p class="liquid-glass-eyebrow">{{ $variantLabel }}</p>
                    <h2
                        class="mt-4 text-4xl leading-tight font-black text-balance sm:text-5xl"
                    >
                        {{ $heading }}
                    </h2>
                </div>

                @if ($summary)
                    <p
                        class="text-lg leading-8 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_72%,transparent)]"
                    >
                        {{ $summary }}
                    </p>
                @endif
            </div>

            @if ($items !== [])
                <div class="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($items as $item)
                        <article class="liquid-glass-card">
                            @if (! empty($item['image']))
                                <img
                                    src="{{ $item['image'] }}"
                                    alt=""
                                    width="720"
                                    height="480"
                                    loading="lazy"
                                    decoding="async"
                                    class="aspect-[3/2] w-full rounded-[calc(var(--liquid-glass-radius)-0.3rem)] object-cover"
                                />
                            @endif

                            <div @class(['pt-5' => ! empty($item['image'])])>
                                <div
                                    class="flex flex-wrap gap-2 text-xs font-bold tracking-wide text-[color-mix(in_srgb,var(--liquid-glass-foreground)_58%,transparent)] uppercase"
                                >
                                    @if (! empty($item['type']))
                                        <span>{{ $item['type'] }}</span>
                                    @endif

                                    @if (! empty($item['publishedDate']))
                                        <span>
                                            {{ $item['publishedDate'] }}
                                        </span>
                                    @elseif (! empty($item['publishedAt']))
                                        <span>{{ $item['publishedAt'] }}</span>
                                    @endif
                                </div>

                                <h3
                                    class="mt-3 text-2xl leading-tight font-black"
                                >
                                    @if (! empty($item['url']))
                                        <a
                                            href="{{ $item['url'] }}"
                                            class="hover:text-[var(--theme-primary)]"
                                        >
                                            {{ $item['title'] }}
                                        </a>
                                    @else
                                        {{ $item['title'] }}
                                    @endif
                                </h3>

                                @if (! empty($item['summary']))
                                    <p
                                        class="mt-4 text-sm leading-7 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_70%,transparent)]"
                                    >
                                        {{ $item['summary'] }}
                                    </p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="liquid-glass-card mt-10">
                    <p class="text-lg font-bold">
                        {{ __('capell-theme-liquid-glass::generic.listing_empty_title') }}
                    </p>
                    <p
                        class="mt-2 text-sm leading-7 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_70%,transparent)]"
                    >
                        {{ __('capell-theme-liquid-glass::generic.listing_empty_summary') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</section>
