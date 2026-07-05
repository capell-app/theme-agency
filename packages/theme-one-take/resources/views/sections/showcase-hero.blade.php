@php
    $heading = data_get($section, 'heading', __('capell-theme-one-take::sections.featured.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-one-take::sections.featured.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-one-take::sections.featured.launch_title'), 'summary' => __('capell-theme-one-take::sections.featured.launch_summary')],
        ['title' => __('capell-theme-one-take::sections.featured.essay_title'), 'summary' => __('capell-theme-one-take::sections.featured.essay_summary')],
        ['title' => __('capell-theme-one-take::sections.featured.template_title'), 'summary' => __('capell-theme-one-take::sections.featured.template_summary')],
    ]);
@endphp

<section
    id="showcase-hero"
    class="ops-section"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-take::sections.featured.kicker') }}
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
                            height="1200"
                            loading="lazy"
                            decoding="async"
                            class="ops-capture-media"
                        />
                    @else
                        <div
                            class="ops-capture-media ops-capture-media-empty"
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
                        <p>{{ data_get($item, 'summary', '') }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
