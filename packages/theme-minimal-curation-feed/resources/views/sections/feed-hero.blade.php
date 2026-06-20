@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-minimal-curation-feed::sections.featured.product_title'), 'summary' => __('capell-theme-minimal-curation-feed::sections.featured.product_summary')],
        ['title' => __('capell-theme-minimal-curation-feed::sections.featured.design_title'), 'summary' => __('capell-theme-minimal-curation-feed::sections.featured.design_summary')],
        ['title' => __('capell-theme-minimal-curation-feed::sections.featured.advice_title'), 'summary' => __('capell-theme-minimal-curation-feed::sections.featured.advice_summary')],
    ]);
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner editorial-split">
        <div>
            <p class="editorial-kicker">
                {{ __('capell-theme-minimal-curation-feed::sections.featured.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-minimal-curation-feed::sections.featured.heading')) }}
            </h2>
            <p class="editorial-lede">
                {{ data_get($section, 'summary', __('capell-theme-minimal-curation-feed::sections.featured.summary')) }}
            </p>
        </div>
        <div class="editorial-grid">
            @foreach ($items as $item)
                <article class="editorial-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
