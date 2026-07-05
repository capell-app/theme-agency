@php
    $heading = data_get($section, 'heading', __('capell-theme-open-studio::sections.discipline_filters.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-open-studio::sections.discipline_filters.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-open-studio::sections.discipline_filters.product_title'), 'summary' => __('capell-theme-open-studio::sections.discipline_filters.product_summary')],
        ['title' => __('capell-theme-open-studio::sections.discipline_filters.brand_title'), 'summary' => __('capell-theme-open-studio::sections.discipline_filters.brand_summary')],
        ['title' => __('capell-theme-open-studio::sections.discipline_filters.motion_title'), 'summary' => __('capell-theme-open-studio::sections.discipline_filters.motion_summary')],
        ['title' => __('capell-theme-open-studio::sections.discipline_filters.engineering_title'), 'summary' => __('capell-theme-open-studio::sections.discipline_filters.engineering_summary')],
    ]);
@endphp

<section
    id="discipline-filters"
    class="csp-section"
>
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-open-studio::sections.discipline_filters.heading') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="csp-lede">{{ $summary }}</p>

        <div class="csp-pill-row">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href', '#project-feed'));
                @endphp

                <a
                    class="csp-pill"
                    href="{{ $itemUrl }}"
                >
                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    <span class="csp-pill-summary">
                        {{ data_get($item, 'summary', '') }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
