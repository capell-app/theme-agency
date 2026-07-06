@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.nominees.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-gold-rush::sections.nominees.summary'));
    // Payload cap (Wave 2 §0.3): carousels ship at most 20 items; extra
    // entries belong on the archive/content-listing page, not a bigger payload.
    $items = collect(data_get($section, 'items', []))->take(20)->values();
@endphp

<section
    id="newest-nominees"
    class="sbs-section"
>
    <div class="sbs-section-inner">
        <div class="sbs-heading-row">
            <div>
                <p class="sbs-kicker">
                    {{ __('capell-theme-gold-rush::sections.nominees.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="sbs-lede">{{ $summary }}</p>
            </div>

            <div
                class="swiper-controls sbs-nominee-carousel-controls"
                data-carousel-controls="gold-rush-nominees"
            >
                <button
                    type="button"
                    class="swiper-button-prev sbs-carousel-arrow"
                    aria-label="{{ __('capell-theme-gold-rush::sections.nominees.carousel_prev') }}"
                ></button>
                <button
                    type="button"
                    class="swiper-button-next sbs-carousel-arrow"
                    aria-label="{{ __('capell-theme-gold-rush::sections.nominees.carousel_next') }}"
                ></button>
            </div>
        </div>

        <div
            class="swiper sbs-nominee-carousel"
            data-carousel-id="gold-rush-nominees"
            data-carousel-per-view="1"
            data-carousel-navigation="true"
            data-carousel-breakpoints='{"640":{"slidesPerView":2},"1024":{"slidesPerView":3}}'
        >
            <div class="swiper-wrapper sbs-leaderboard-track">
                @foreach ($items as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $overall = data_get($item, 'care_note', data_get($item, 'score'));
                    @endphp

                    <article class="swiper-slide sbs-nominee-card">
                        @if (filled($itemImage))
                            <div class="sbs-nominee-media">
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    class="sbs-nominee-media-image"
                                />
                                <span class="sbs-nominee-rank">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                        @else
                            <div
                                class="sbs-nominee-media"
                                aria-hidden="true"
                            >
                                <span class="sbs-nominee-rank">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                        @endif

                        <div class="sbs-nominee-body">
                            <p class="sbs-nominee-meta">
                                {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-gold-rush::sections.nominees.default_meta'))) }}
                            </p>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="sbs-title-link sbs-nominee-card-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                @endif
                            </h3>
                            <p>
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                            @if (filled($overall))
                                <p class="sbs-nominee-score-line">
                                    <span>
                                        {{ __('capell-theme-gold-rush::sections.nominees.overall_label') }}
                                    </span>
                                    <span class="sbs-score-value">
                                        {{ $overall }}
                                    </span>
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div
                class="swiper-pagination sbs-nominee-carousel-pagination"
            ></div>
        </div>
    </div>
</section>
