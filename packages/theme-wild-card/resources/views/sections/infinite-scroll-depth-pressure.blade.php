@php
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.depth.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.depth.summary'));
    $items = collect(data_get($section, 'items', []))->take(50);
@endphp

{{--
    §0.8 modern-CSS guardrail: `animation-timeline: scroll()` is a pure
    visual enhancement layered onto an already-usable static grid — no
    infinite-scroll pagination logic runs here at all, this is a depth
    illusion only. Browsers without scroll-driven animation support simply
    keep the static (unshadowed) card styling from `.exd-depth-card`, so the
    section degrades gracefully rather than requiring the feature.
--}}
<section
    id="infinite-scroll-depth-pressure"
    class="exd-section"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.depth.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        <div
            class="exd-depth-grid"
            data-infinite-scroll-depth-pressure
        >
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                @endphp

                @if (filled($itemUrl))
                    <a
                        class="exd-card exd-depth-card"
                        href="{{ $itemUrl }}"
                    >
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="800"
                                height="600"
                                loading="lazy"
                                decoding="async"
                                class="exd-media"
                            />
                        @else
                            <div
                                class="exd-media exd-media-empty"
                                aria-hidden="true"
                            ></div>
                        @endif
                        <div class="exd-card-body">
                            <p class="exd-meta">
                                {{ data_get($item, 'meta', '') }}
                            </p>
                            <h3>{{ $itemTitle }}</h3>
                            <p>{{ data_get($item, 'summary', '') }}</p>
                        </div>
                    </a>
                @else
                    <article class="exd-card exd-depth-card">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="800"
                                height="600"
                                loading="lazy"
                                decoding="async"
                                class="exd-media"
                            />
                        @else
                            <div
                                class="exd-media exd-media-empty"
                                aria-hidden="true"
                            ></div>
                        @endif
                        <div class="exd-card-body">
                            <p class="exd-meta">
                                {{ data_get($item, 'meta', '') }}
                            </p>
                            <h3>{{ $itemTitle }}</h3>
                            <p>{{ data_get($item, 'summary', '') }}</p>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>
