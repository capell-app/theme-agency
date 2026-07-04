@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-one-page-showcase::sections.topics.product_title'), 'summary' => __('capell-theme-one-page-showcase::sections.topics.product_summary')],
        ['title' => __('capell-theme-one-page-showcase::sections.topics.design_title'), 'summary' => __('capell-theme-one-page-showcase::sections.topics.design_summary')],
        ['title' => __('capell-theme-one-page-showcase::sections.topics.advice_title'), 'summary' => __('capell-theme-one-page-showcase::sections.topics.advice_summary')],
        ['title' => __('capell-theme-one-page-showcase::sections.topics.culture_title'), 'summary' => __('capell-theme-one-page-showcase::sections.topics.culture_summary')],
    ]);
@endphp

<section
    id="category-tabs"
    class="ops-section"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-page-showcase::sections.topics.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-one-page-showcase::sections.topics.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="ops-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="ops-tab-row">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href', '/#one-page-grid'));
                @endphp

                <a
                    class="ops-tab"
                    href="{{ $itemUrl }}"
                >
                    <strong>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </strong>
                    <span>{{ data_get($item, 'summary', '') }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
