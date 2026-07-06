@php
    /**
     * archive-wall-index --compact variant: a uniform, tightly packed grid
     * with no varied spans — for secondary archive pages where a dense,
     * evenly ranked listing reads better than the contact-sheet wall.
     */
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.archive_wall_index.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.archive_wall_index.summary'));
    $items = collect(data_get($section, 'items', []))->take(50);
@endphp

<section
    id="archive-wall-index"
    class="mva-section"
    data-widget="archive-wall-index"
    data-variant="compact"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.archive_wall_index.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        @if ($items->isNotEmpty())
            <div
                class="mva-wall mva-wall-compact"
                style="margin-top: 2rem"
            >
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <a
                        href="{{ $itemUrl ?? '#' }}"
                        class="mva-wall-tile"
                    >
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="mva-wall-tile-image"
                            />
                        @endif
                        <span class="mva-wall-tile-caption">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
