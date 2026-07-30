@php
    /**
     * education-upsell-cta (Wave 4c signature widget): the course/teardown
     * list rendered with a single lead card promoted into a wide conversion
     * banner, so the education upsell reads as one strong invitation rather
     * than three equal cards -- the promoted item is always the first item
     * in the payload (deterministic; §0.1).
     */
    $heading = data_get($section, 'heading', 'Learn from the portfolios you admire');
    $summary = data_get($section, 'summary', 'Members get the long-form interviews and process teardowns behind the featured work.');
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-agency::sections.authors.design_title'), 'summary' => __('capell-theme-agency::sections.authors.design_summary')],
        ['title' => __('capell-theme-agency::sections.authors.advice_title'), 'summary' => __('capell-theme-agency::sections.authors.advice_summary')],
        ['title' => __('capell-theme-agency::sections.authors.company_title'), 'summary' => __('capell-theme-agency::sections.authors.company_summary')],
    ]))->take(50)->values();

    $leadItem = $items->first();
    $restItems = $items->slice(1)->values();
    $ctaLabel = data_get($section, 'cta_label', __('capell-theme-agency::sections.spotlight.upsell_cta_label'));
@endphp

<section
    id="learn"
    class="ppc-section ppc-section-field"
    data-widget="education-upsell-cta"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">{{ __('capell-theme-agency::sections.authors.kicker') }}</p>
        <h2>{{ $heading }}</h2>
        <p class="ppc-lede">{{ $summary }}</p>

        @if ($leadItem !== null)
            @php
                $leadUrl = data_get($leadItem, 'url', data_get($leadItem, 'href', '#learn'));
            @endphp

            <article class="ppc-card ppc-course-card ppc-course-lead">
                <p class="ppc-chip">{{ __('capell-theme-agency::sections.spotlight.upsell_lead_label') }}</p>
                <h3>
                    <a
                        class="ppc-title-link"
                        href="{{ $leadUrl }}"
                    >
                        {{ data_get($leadItem, 'title', data_get($leadItem, 'name', '')) }}
                    </a>
                </h3>
                <p>{{ data_get($leadItem, 'summary', '') }}</p>
                <a
                    class="ppc-button"
                    href="{{ $leadUrl }}"
                >
                    {{ $ctaLabel }}
                </a>
            </article>
        @endif

        @if ($restItems->isNotEmpty())
            <div class="ppc-grid">
                @foreach ($restItems as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    @endphp

                    <article class="ppc-card ppc-course-card">
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
        @endif
    </div>
</section>
