@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.categories.women'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.categories.women_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.categories.men'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.categories.men_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.categories.objects'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.categories.objects_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.categories.stores'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.categories.stores_summary')],
    ]);
@endphp

<section class="luxury-section">
    <div class="luxury-section-inner">
        <p class="luxury-kicker">
            {{ __('capell-theme-quiet-luxury-retail::sections.categories.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.categories.heading')) }}
        </h2>
        <div class="luxury-grid">
            @foreach ($items as $item)
                <article class="luxury-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
