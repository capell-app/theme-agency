{{--
    category-tabs-pagination (Wave 4c signature widget, mechanic: dossier
    pages). Categories read as page ranges of the dossier ("pp. 01-12")
    rather than plain pills, and behave as a real ARIA tablist wired to the
    shared Foundation `tabs.js` module (role="tablist"/"tab"/"tabpanel",
    keyboard arrow navigation, one panel visible at a time). This is the
    "default" (link-row) variant for themes that keep every category as its
    own page anchor; the "--tablist" variant swaps in live in-page panels.
--}}
@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-one-take::sections.topics.product_title'), 'summary' => __('capell-theme-one-take::sections.topics.product_summary'), 'range' => __('capell-theme-one-take::sections.topics.product_range')],
        ['title' => __('capell-theme-one-take::sections.topics.design_title'), 'summary' => __('capell-theme-one-take::sections.topics.design_summary'), 'range' => __('capell-theme-one-take::sections.topics.design_range')],
        ['title' => __('capell-theme-one-take::sections.topics.advice_title'), 'summary' => __('capell-theme-one-take::sections.topics.advice_summary'), 'range' => __('capell-theme-one-take::sections.topics.advice_range')],
        ['title' => __('capell-theme-one-take::sections.topics.culture_title'), 'summary' => __('capell-theme-one-take::sections.topics.culture_summary'), 'range' => __('capell-theme-one-take::sections.topics.culture_range')],
    ]);
@endphp

<section
    id="category-tabs"
    class="ops-section"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-take::sections.topics.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-one-take::sections.topics.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="ops-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="ops-tab-row">
            @foreach ($items as $item)
                <a
                    class="ops-tab ops-dossier-tab"
                    href="{{ data_get($item, 'url', data_get($item, 'href', '/#one-page-grid')) }}"
                >
                    <span
                        class="ops-dossier-tab-range"
                        aria-hidden="true"
                    >
                        {{ data_get($item, 'range', __('capell-theme-one-take::sections.topics.default_range')) }}
                    </span>
                    <strong>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </strong>
                    <span>{{ data_get($item, 'summary', '') }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
