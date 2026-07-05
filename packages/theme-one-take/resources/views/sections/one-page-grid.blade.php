@php
    $heading = data_get($section, 'heading', __('capell-theme-one-take::sections.stories.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-one-take::sections.stories.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-one-take::sections.stories.product_title'), 'summary' => __('capell-theme-one-take::sections.stories.product_summary'), 'meta' => __('capell-theme-one-take::sections.stories.product_meta')],
        ['title' => __('capell-theme-one-take::sections.stories.design_title'), 'summary' => __('capell-theme-one-take::sections.stories.design_summary'), 'meta' => __('capell-theme-one-take::sections.stories.design_meta')],
        ['title' => __('capell-theme-one-take::sections.stories.advice_title'), 'summary' => __('capell-theme-one-take::sections.stories.advice_summary'), 'meta' => __('capell-theme-one-take::sections.stories.advice_meta')],
    ]);
@endphp

<section
    id="one-page-grid"
    class="ops-section"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-take::sections.stories.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="ops-lede">{{ $summary }}</p>

        <div class="ops-grid ops-grid-captures">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="ops-capture-frame">
                    <div
                        class="ops-capture-chrome"
                        aria-hidden="true"
                    >
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    @if (filled($itemImage))
                        <img
                            src="{{ $itemImage }}"
                            alt="{{ $itemAlt }}"
                            width="900"
                            height="1400"
                            loading="lazy"
                            decoding="async"
                            class="ops-capture-media ops-capture-media-tall"
                        />
                    @else
                        <div
                            class="ops-capture-media ops-capture-media-tall ops-capture-media-empty"
                            aria-hidden="true"
                        ></div>
                    @endif
                    <div class="ops-capture-body">
                        <h3>
                            @if (filled($itemUrl))
                                <a
                                    class="ops-title-link"
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
                        <div class="ops-tag-row">
                            <span class="ops-tag">
                                {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-one-take::sections.stories.default_meta'))) }}
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
