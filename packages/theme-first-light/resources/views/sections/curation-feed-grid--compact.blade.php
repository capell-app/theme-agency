{{--
    curation-feed-grid, `compact` variant: a tighter contact sheet (smaller
    tiles, no per-item caption, meta on hover/focus only) for placements where
    the default caption-under-thumbnail layout would run too tall -- e.g. a
    "more like this" rail on the detail page. Same lightbox.js contract, same
    `curation-feed` group so Next/Previous still cycles the full reel.
--}}

@php
    $items = collect(data_get($section, 'items', data_get($section, 'entries', [])))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->take(50)
        ->values();
@endphp

<section
    id="curation-feed-grid"
    class="mcf-section"
    data-widget="curation-feed-grid"
    data-variant="compact"
>
    <div class="mcf-section-inner mcf-section-inner-wide">
        <p class="mcf-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-first-light::sections.grid.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-first-light::sections.grid.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="mcf-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="mcf-contact-sheet mcf-contact-sheet-compact">
                @foreach ($items as $item)
                    @php
                        $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', $itemTitle);
                    @endphp

                    <figure class="mcf-contact-frame mcf-contact-frame-compact">
                        @if (filled($itemImage))
                            <a
                                href="{{ $itemImage }}"
                                class="lightbox mcf-contact-trigger"
                                data-lightbox="{{ $itemImage }}"
                                data-group="curation-feed"
                                data-type="image"
                                data-title="{{ $itemTitle }}"
                                aria-label="{{ __('capell-theme-first-light::sections.grid.open_lightbox', ['title' => $itemTitle]) }}"
                            >
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    class="mcf-contact-media"
                                />
                                <span
                                    class="mcf-contact-caption-overlay"
                                    >{{ $itemTitle }}</span
                                >
                            </a>
                        @else
                            <span
                                class="mcf-contact-media mcf-capture-media-empty"
                                aria-hidden="true"
                            ></span>
                        @endif
                    </figure>
                @endforeach
            </div>
        @else
            <div class="mcf-empty">
                <p class="mcf-empty-heading">
                    {{ __('capell-theme-first-light::sections.grid.empty_heading') }}
                </p>
                <p class="mcf-empty-note">
                    {{ __('capell-theme-first-light::sections.grid.empty') }}
                </p>
            </div>
        @endif
    </div>
</section>
