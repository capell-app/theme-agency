@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-premium-portfolio-collection::sections.authors.design_title'), 'summary' => __('capell-theme-premium-portfolio-collection::sections.authors.design_summary')],
        ['title' => __('capell-theme-premium-portfolio-collection::sections.authors.advice_title'), 'summary' => __('capell-theme-premium-portfolio-collection::sections.authors.advice_summary')],
        ['title' => __('capell-theme-premium-portfolio-collection::sections.authors.company_title'), 'summary' => __('capell-theme-premium-portfolio-collection::sections.authors.company_summary')],
    ]);
@endphp

<section
    id="learn"
    class="ppc-section ppc-section-field"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ __('capell-theme-premium-portfolio-collection::sections.authors.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-premium-portfolio-collection::sections.authors.heading')) }}
        </h2>
        <p class="ppc-lede">
            {{ data_get($section, 'summary', __('capell-theme-premium-portfolio-collection::sections.authors.summary')) }}
        </p>
        <div class="ppc-grid">
            @foreach ($items as $item)
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
    </div>
</section>
