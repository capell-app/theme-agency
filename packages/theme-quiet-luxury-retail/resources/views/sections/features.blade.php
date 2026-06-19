@php
    $heading = data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.features.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-quiet-luxury-retail::sections.features.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.features.serum_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.features.serum_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.features.serum_meta')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.features.scent_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.features.scent_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.features.scent_meta')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.features.wash_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.features.wash_summary'), 'meta' => __('capell-theme-quiet-luxury-retail::sections.features.wash_meta')],
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
