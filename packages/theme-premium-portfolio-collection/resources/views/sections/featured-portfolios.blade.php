@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-premium-portfolio-collection::sections.featured.launch_title'), 'summary' => __('capell-theme-premium-portfolio-collection::sections.featured.launch_summary')],
        ['title' => __('capell-theme-premium-portfolio-collection::sections.featured.essay_title'), 'summary' => __('capell-theme-premium-portfolio-collection::sections.featured.essay_summary')],
        ['title' => __('capell-theme-premium-portfolio-collection::sections.featured.template_title'), 'summary' => __('capell-theme-premium-portfolio-collection::sections.featured.template_summary')],
    ]);
@endphp

<section
    id="featured"
    class="ppc-section"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ __('capell-theme-premium-portfolio-collection::sections.featured.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-premium-portfolio-collection::sections.featured.heading')) }}
        </h2>
        <p class="ppc-lede">
            {{ data_get($section, 'summary', __('capell-theme-premium-portfolio-collection::sections.featured.summary')) }}
        </p>
        <div class="ppc-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $award = data_get($item, 'award');
                @endphp

                <article class="ppc-card">
                    <figure class="ppc-plate">
                        <div class="ppc-plate-frame">
                            @if (filled($award))
                                <span class="ppc-badge">{{ $award }}</span>
                            @endif

                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    width="400"
                                    height="300"
                                    class="ppc-plate-media"
                                />
                            @else
                                <div
                                    class="ppc-plate-media ppc-plate-media-empty"
                                    aria-hidden="true"
                                ></div>
                            @endif
                        </div>
                    </figure>
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="ppc-title-link"
                                href="{{ $itemUrl }}"
                            >
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        @endif
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
