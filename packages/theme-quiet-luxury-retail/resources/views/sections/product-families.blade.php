@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.families.skin'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.families.skin_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.families.fragrance'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.families.fragrance_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.families.body'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.families.body_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.families.home'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.families.home_summary')],
    ]);
@endphp

<section class="luxury-section">
    <div class="luxury-section-inner">
        <p class="luxury-kicker">
            {{ __('capell-theme-quiet-luxury-retail::sections.families.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.families.heading')) }}
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
