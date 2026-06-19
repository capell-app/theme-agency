@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.features.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-quiet-luxury-retail::sections.features.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.features.coat_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.features.coat_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.features.coat_meta')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.features.shirt_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.features.shirt_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.features.shirt_meta')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.features.trouser_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.features.trouser_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.features.trouser_meta')],
    ]);
@endphp

<section class="luxury-section">
    <div class="luxury-section-inner">
        <p class="luxury-kicker">
            {{ __('capell-theme-quiet-luxury-retail::sections.features.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="luxury-lede">{{ $summary }}</p>

        <div class="luxury-grid">
            @foreach ($items as $item)
                <article class="luxury-card luxury-product-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <div class="luxury-product-specs">
                        <span>
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-quiet-luxury-retail::sections.features.default_meta'))) }}
                        </span>
                        <span>
                            {{ data_get($item, 'care_note', __('capell-theme-quiet-luxury-retail::sections.features.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
