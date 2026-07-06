@php
    /**
     * video-preview-grid (Wave 4b signature widget): the cinema-archive index
     * of every filed project, each thumbnail a shared hover-video-poster
     * primitive (Wave 2.7) so a still swaps to a muted, looping preview on
     * hover/tap rather than reinventing hover-video swap logic per theme.
     * Payload cap §0.3: content-listing grids are capped at 50 items.
     */
    $items = collect(data_get($section, 'items', data_get($section, 'posts', [])))->take(50);
@endphp

<section
    id="content-listing"
    class="mva-section"
    data-widget="video-preview-grid"
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
                class="mva-preview-grid"
                style="margin-top: 2rem"
            >
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemVideo = data_get($item, 'videoSrc', data_get($item, 'video'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article class="mva-preview-card">
                        <a
                            href="{{ $itemUrl ?? '#' }}"
                            class="mva-preview-card-link"
                        >
                            @if (filled($itemImage) && filled($itemVideo))
                                <x-capell-theme-foundation::display.hover-video-poster
                                    :poster="$itemImage"
                                    :video-src="$itemVideo"
                                    :alt="$itemAlt"
                                    aspect-ratio="16/10"
                                    class="mva-preview-frame"
                                />
                            @elseif (filled($itemImage))
                                <span class="mva-preview-frame">
                                    <img
                                        src="{{ $itemImage }}"
                                        alt="{{ $itemAlt }}"
                                        loading="lazy"
                                        decoding="async"
                                        class="mva-preview-frame-image"
                                    />
                                </span>
                            @endif

                            <span
                                class="mva-preview-numeral"
                                aria-hidden="true"
                            >
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="mva-preview-body">
                                <span class="mva-preview-meta">
                                    {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                                </span>
                                <span class="mva-preview-title">
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                </span>
                                <span class="mva-preview-summary">
                                    {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                                </span>
                            </span>
                        </a>
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
