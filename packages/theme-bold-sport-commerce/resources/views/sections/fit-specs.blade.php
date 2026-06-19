@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-bold-sport-commerce::sections.fit.fit_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.fit.fit_summary')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.fit.color_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.fit.color_summary')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.fit.sale_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.fit.sale_summary')],
    ]);
@endphp

<section
    class="sport-section"
    style="background: var(--sport-field)"
>
    <div class="sport-section-inner">
        <p class="sport-kicker">
            {{ __('capell-theme-bold-sport-commerce::sections.fit.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-bold-sport-commerce::sections.fit.heading')) }}
        </h2>
        <p class="sport-lede">
            {{ data_get($section, 'summary', __('capell-theme-bold-sport-commerce::sections.fit.summary')) }}
        </p>
        <div class="sport-grid">
            @foreach ($items as $item)
                <article class="sport-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
