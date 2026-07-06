@php
    /**
     * video-preview-grid --rows variant: the same hover-video-poster preview
     * mechanic as the default grid, laid out as a dense numbered index row
     * rather than a card grid — for archive pages that want a scannable list
     * rhythm instead of a wall of thumbnails. Payload cap §0.3: capped at 50.
     */
    $items = collect(data_get($section, 'items', data_get($section, 'posts', [])))->take(50);
@endphp

<section
    id="content-listing"
    class="mva-section"
    data-widget="video-preview-grid"
    data-variant="rows"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-reel-room::sections.listing.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="mva-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div
                class="mva-index"
                style="margin-top: 2rem"
            >
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemVideo = data_get($item, 'videoSrc', data_get($item, 'video'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article
                        class="mva-index-row {{ filled($itemImage) ? 'mva-index-row-media' : '' }}"
                    >
                        <span
                            class="mva-numeral"
                            aria-hidden="true"
                        >
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        @if (filled($itemImage) && filled($itemVideo))
                            <x-capell-theme-foundation::display.hover-video-poster
                                :poster="$itemImage"
                                :video-src="$itemVideo"
                                :alt="$itemAlt"
                                aspect-ratio="16/10"
                                class="mva-index-thumb"
                            />
                        @elseif (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="mva-index-thumb"
                            />
                        @endif

                        <div>
                            <p>
                                {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                            </p>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="mva-winner-title-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                @endif
                            </h3>
                        </div>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @else
            <p
                class="mva-lede"
                style="margin-top: 2rem"
            >
                {{ __('capell-theme-reel-room::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
