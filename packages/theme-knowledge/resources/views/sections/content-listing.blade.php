@php
    $listingItems = $items ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-content-listing bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-5 md:grid-cols-[0.75fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-accent)] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.archive_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[var(--site-theme-foreground)]"
                >
                    {{ $heading ?? $section->heading }}
                </h2>
            </div>

            @if (($summary ?? $section->summary ?? null) !== null)
                <p
                    class="max-w-2xl text-lg leading-8 text-slate-600 md:justify-self-end"
                >
                    {{ $summary ?? $section->summary }}
                </p>
            @endif
        </div>

        @if ($listingItems === [])
            <div
                class="mt-10 border border-dashed border-[var(--site-theme-primary-soft)] bg-[var(--site-theme-primary-panel)] p-8"
            >
                <p class="text-sm font-black text-[var(--site-theme-primary-strong)]">
                    {{ __('capell-theme-knowledge::generic.listing_empty_title') }}
                </p>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    {{ __('capell-theme-knowledge::generic.listing_empty_summary') }}
                </p>
            </div>
        @else
            <div class="mt-10 grid gap-5">
                @foreach ($listingItems as $index => $item)
                    <article
                        class="group grid overflow-hidden border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-[var(--site-theme-primary)] hover:shadow-xl lg:grid-cols-[0.72fr_1.35fr_0.62fr]"
                    >
                        @if ($item['image'] ?? $item['imageUrl'] ?? null)
                            <img
                                src="{{ $item['image'] ?? $item['imageUrl'] }}"
                                alt=""
                                class="h-full min-h-48 w-full object-cover"
                            />
                        @else
                            <div
                                class="min-h-48 bg-[var(--site-theme-primary-contrast)] p-5 text-white"
                                role="img"
                                aria-label="{{ __('capell-theme-knowledge::generic.listing_image_fallback_label') }}"
                            >
                                <span
                                    class="block h-3 w-20 bg-[var(--site-theme-accent)]"
                                    aria-hidden="true"
                                ></span>
                                <p class="mt-8 max-w-48 text-sm font-black leading-6">
                                    {{ __('capell-theme-knowledge::generic.listing_image_fallback_title') }}
                                </p>
                                <p class="mt-2 max-w-48 text-xs leading-5 text-white/75">
                                    {{ __('capell-theme-knowledge::generic.listing_image_fallback_summary') }}
                                </p>
                                <div class="mt-10 space-y-2">
                                    <span
                                        class="block h-2 w-full bg-white/35"
                                        aria-hidden="true"
                                    ></span>
                                    <span
                                        class="block h-2 w-5/6 bg-white/25"
                                        aria-hidden="true"
                                    ></span>
                                    <span
                                        class="block h-2 w-2/3 bg-white/25"
                                        aria-hidden="true"
                                    ></span>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col p-5">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-primary)] uppercase"
                            >
                                {{ $item['type'] ?? __('capell-theme-knowledge::generic.article_signal') }}
                            </p>
                            <h3 class="mt-3 text-xl font-black text-[var(--site-theme-foreground)]">
                                @if ($item['url'] ?? null)
                                    <a
                                        href="{{ $item['url'] }}"
                                        class="hover:text-[var(--site-theme-primary)]"
                                    >
                                        {{ $item['title'] }}
                                    </a>
                                @else
                                    {{ $item['title'] }}
                                @endif
                            </h3>
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ $item['summary'] ?? $item['description'] ?? '' }}
                            </p>
                            <div
                                class="mt-auto flex flex-wrap gap-2 pt-5 text-xs font-bold"
                            >
                                @foreach (($item['meta'] ?? []) ?: [__('capell-theme-knowledge::generic.library_signal')] as $meta)
                                    <span
                                        class="bg-[var(--site-theme-primary-panel)] px-3 py-1 text-[var(--site-theme-primary-strong)]"
                                    >
                                        {{ $meta }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div
                            class="border-t border-slate-200 bg-[var(--site-theme-surface)] p-5 lg:border-t-0 lg:border-l"
                        >
                            <p
                                class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                            >
                                {{ __('capell-theme-knowledge::generic.reading_queue_label') }}
                            </p>
                            <p
                                class="mt-3 font-mono text-3xl font-black text-[var(--site-theme-accent)]"
                            >
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </p>
                            <p class="mt-3 text-sm font-bold text-[var(--site-theme-foreground)]">
                                {{ __('capell-theme-knowledge::generic.saved_signal') }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
