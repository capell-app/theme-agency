@php
    $results = $section->results ?? $section->items ?? [];
    $filters = $section->filters ?? [];
@endphp

<section class="theme-section theme-section-search bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-8 lg:grid-cols-[0.75fr_1.25fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[var(--retail-primary)] uppercase"
                >
                    {{ __('capell-theme-commerce::generic.search_label') }}
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
                @forelse ($results as $result)
                    <a
                        href="{{ $result['url'] ?? '#' }}"
                        class="grid gap-4 rounded-2xl border border-[var(--retail-line)] bg-[var(--retail-surface)] p-4 text-[var(--retail-ink)] sm:grid-cols-[0.28fr_1fr]"
                    >
                        @if (! empty($result['image']) || ! empty($result['imageUrl']))
                            <img
                                src="{{ $result['image'] ?? $result['imageUrl'] }}"
                                alt="{{ $result['imageAlt'] ?? $result['title'] ?? '' }}"
                                width="320"
                                height="320"
                                loading="lazy"
                                decoding="async"
                                class="aspect-square w-full rounded-xl object-cover"
                            />
                        @endif

                        <div>
                            <p
                                class="text-xs font-black text-[var(--retail-primary)] uppercase"
                            >
                                {{ $result['type'] ?? __('capell-theme-commerce::generic.product_label') }}
                            </p>
                            <h3 class="mt-2 text-xl font-black">
                                {{ $result['title'] ?? '' }}
                            </h3>
                            @if (! empty($result['summary']))
                                <p
                                    class="mt-2 text-sm leading-6 text-stone-600"
                                >
                                    {{ $result['summary'] }}
                                </p>
                            @endif

                            @if (! empty($result['price']))
                                <p
                                    class="mt-3 text-sm font-black text-[var(--retail-accent)]"
                                >
                                    {{ $result['price'] }}
                                </p>
                            @endif
                        </div>
                    </a>
                @empty
                    <div
                        class="rounded-2xl border border-dashed border-[var(--retail-line)] p-6"
                    >
                        <p
                            class="text-sm font-black text-[var(--retail-primary)] uppercase"
                        >
                            {{ __('capell-theme-commerce::generic.search_empty_title') }}
                        </p>
                        <p class="mt-3 text-sm leading-6 text-stone-600">
                            {{ __('capell-theme-commerce::generic.search_empty_summary') }}
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
