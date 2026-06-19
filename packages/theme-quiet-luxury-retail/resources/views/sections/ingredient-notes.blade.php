@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.ingredients.botanical_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.ingredients.botanical_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.ingredients.botanical_meta')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.ingredients.glass_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.ingredients.glass_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.ingredients.glass_meta')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.ingredients.scent_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.ingredients.scent_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.ingredients.scent_meta')],
    ]));
@endphp

<section class="luxury-section">
    <div class="luxury-section-inner">
        <p class="luxury-kicker">
            {{ __('capell-theme-quiet-luxury-retail::sections.ingredients.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.ingredients.heading')) }}
        </h2>
        <div class="luxury-grid">
            @foreach ($stories as $story)
                <article class="luxury-card">
                    <p class="luxury-meta">
                        {{ data_get($story, 'meta', data_get($story, 'category', '')) }}
                    </p>
                    <h3>
                        {{ data_get($story, 'title', data_get($story, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($story, 'summary', data_get($story, 'description', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
