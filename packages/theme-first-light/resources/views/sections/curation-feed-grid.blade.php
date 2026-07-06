{{--
    curation-feed-grid: the "lightbox reel" contact sheet -- a dense grid of
    capture thumbnails (grid auto-fit, masonry-safe, no true CSS masonry) that
    opens the shared Foundation lightbox (lightbox.js) for deep inspection,
    Next/Previous cycling within the `curation-feed` group. Capped at 50
    items per the platform §0.3 payload guardrail.
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
    data-variant="default"
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
            <div class="mcf-contact-sheet">
                @foreach ($items as $item)
                    @php
                        $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', $itemTitle);
                        $itemMeta = data_get($item, 'meta', data_get($item, 'category'));
                    @endphp

                    <figure class="mcf-contact-frame">
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
                            </a>
                        @else
                            <span
                                class="mcf-contact-media mcf-capture-media-empty"
                                aria-hidden="true"
                            ></span>
                        @endif

                        <figcaption class="mcf-contact-caption">
                            <span class="mcf-tiny">
                                @if (filled($itemUrl))
                                    <a
                                        class="mcf-title-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ $itemTitle }}
                                    </a>
                                @else
                                    {{ $itemTitle }}
                                @endif
                            </span>
                            @if (filled($itemMeta))
                                <span class="mcf-meta">{{ $itemMeta }}</span>
                            @endif
                        </figcaption>
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
