@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section
    id="content-listing"
    class="exd-section exd-section-paper"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-experimental-directory::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-experimental-directory::sections.listing.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="exd-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="exd-grid">
                @foreach ($items as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                    @endphp

                    @if (filled($itemUrl))
                        <a
                            class="exd-card"
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
                                    {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                                </p>
                                <h3>{{ $itemTitle }}</h3>
                                <p>
                                    {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                                </p>
                            </div>
                        </a>
                    @else
                        <article class="exd-card">
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
                                    {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                                </p>
                                <h3>{{ $itemTitle }}</h3>
                                <p>
                                    {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                                </p>
                            </div>
                        </article>
                    @endif
                @endforeach
            </div>
        @else
            <div class="exd-empty">
                <p class="exd-empty-mark">
                    {{ __('capell-theme-experimental-directory::sections.listing.empty_mark') }}
                </p>
                <p class="exd-empty-title">
                    {{ __('capell-theme-experimental-directory::sections.listing.empty_title') }}
                </p>
                <p class="exd-empty-note">
                    {{ __('capell-theme-experimental-directory::sections.listing.empty') }}
                </p>
            </div>
        @endif
    </div>
</section>
