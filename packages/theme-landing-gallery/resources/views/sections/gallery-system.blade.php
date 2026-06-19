@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-landing-gallery::sections.authors.product_title'), 'summary' => __('capell-theme-landing-gallery::sections.authors.product_summary')],
        ['title' => __('capell-theme-landing-gallery::sections.authors.advice_title'), 'summary' => __('capell-theme-landing-gallery::sections.authors.advice_summary')],
        ['title' => __('capell-theme-landing-gallery::sections.authors.company_title'), 'summary' => __('capell-theme-landing-gallery::sections.authors.company_summary')],
    ]);
@endphp

<section
    class="editorial-section"
    style="background: var(--editorial-field)"
>
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-landing-gallery::sections.authors.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-landing-gallery::sections.authors.heading')) }}
        </h2>
        <p class="editorial-lede">
            {{ data_get($section, 'summary', __('capell-theme-landing-gallery::sections.authors.summary')) }}
        </p>
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
