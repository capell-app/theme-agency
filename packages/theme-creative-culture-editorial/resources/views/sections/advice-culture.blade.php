@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-creative-culture-editorial::sections.authors.product_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.authors.product_summary')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.authors.advice_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.authors.advice_summary')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.authors.company_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.authors.company_summary')],
    ]);
@endphp

<section
    id="advice-culture"
    class="cce-section cce-section-field"
>
    <div class="cce-section-inner">
        <p class="cce-kicker">
            {{ __('capell-theme-creative-culture-editorial::sections.authors.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-creative-culture-editorial::sections.authors.heading')) }}
        </h2>
        <p class="cce-lede">
            {{ data_get($section, 'summary', __('capell-theme-creative-culture-editorial::sections.authors.summary')) }}
        </p>
        <div class="cce-grid">
            @foreach ($items as $item)
                <article class="cce-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
