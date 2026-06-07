@php
    $items = $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-portfolio::generic.gallery_lightbox_label');
    $summary ??= $section->summary ?? __('capell-theme-portfolio::generic.gallery_lightbox_summary');
@endphp

<section class="theme-section theme-section-gallery-lightbox bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="portfolio-text-primary text-xs font-black tracking-[0.16em] uppercase"
                >
                    {{ __('capell-theme-portfolio::generic.gallery_lightbox_label') }}
                </p>
                <h2 class="portfolio-text-ink mt-3 text-4xl font-black">
                    {{ $heading }}
                </h2>
            </div>

            @if ($summary)
                <p class="text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($items as $item)
                @php
                    $title = $item['title'] ?? $item['name'] ?? __('capell-theme-portfolio::generic.project_label');
                    $imageUrl = $item['image'] ?? $item['imageUrl'] ?? $item['mediaUrl'] ?? null;
                    $imageAlt = $item['imageAlt'] ?? $item['alt'] ?? $title;
                    $itemUrl = $item['url'] ?? $imageUrl;
                @endphp

                <article
                    class="portfolio-bg-card-soft overflow-hidden rounded-xl border border-slate-200"
                >
                    @if ($imageUrl)
                        <a
                            href="{{ $itemUrl }}"
                            class="block"
                            aria-label="{{ $title }}"
                        >
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $imageAlt }}"
                                width="900"
                                height="700"
                                loading="lazy"
                                decoding="async"
                                class="aspect-[9/7] w-full object-cover"
                            />
                        </a>
                    @else
                        <div
                            class="portfolio-bg-deep flex aspect-[9/7] items-end p-5"
                            aria-hidden="true"
                        >
                            <span
                                class="portfolio-bg-highlight h-20 w-20 rounded-full"
                            ></span>
                        </div>
                    @endif

                    <div class="p-5">
                        <p
                            class="portfolio-text-primary-strong text-xs font-black tracking-[0.16em] uppercase"
                        >
                            {{ $item['type'] ?? __('capell-theme-portfolio::generic.gallery_item_label') }}
                        </p>
                        <h3 class="portfolio-text-ink mt-2 text-lg font-black">
                            {{ $title }}
                        </h3>
                        @if ($item['summary'] ?? $item['description'] ?? null)
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $item['summary'] ?? $item['description'] }}
                            </p>
                        @endif
                    </div>
                </article>
            @empty
                <article
                    class="portfolio-bg-card-soft rounded-xl border border-dashed border-slate-300 p-6 sm:col-span-2 lg:col-span-3"
                >
                    <h3 class="portfolio-text-ink text-lg font-black">
                        {{ __('capell-theme-portfolio::generic.premium_layout_ready') }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('capell-theme-portfolio::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
