@php
    $heading = data_get($section, 'heading', __('capell-theme-minimal-fashion::sections.features.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-minimal-fashion::sections.features.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-minimal-fashion::sections.features.coat_title'), 'summary' => __('capell-theme-minimal-fashion::sections.features.coat_summary'), 'meta' => __('capell-theme-minimal-fashion::sections.features.coat_meta')],
        ['title' => __('capell-theme-minimal-fashion::sections.features.shirt_title'), 'summary' => __('capell-theme-minimal-fashion::sections.features.shirt_summary'), 'meta' => __('capell-theme-minimal-fashion::sections.features.shirt_meta')],
        ['title' => __('capell-theme-minimal-fashion::sections.features.trouser_title'), 'summary' => __('capell-theme-minimal-fashion::sections.features.trouser_summary'), 'meta' => __('capell-theme-minimal-fashion::sections.features.trouser_meta')],
    ]);
@endphp

<section class="fashion-section">
    <div class="fashion-section-inner">
        <p class="fashion-kicker">
            {{ __('capell-theme-minimal-fashion::sections.features.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="fashion-lede">{{ $summary }}</p>

        <div class="fashion-grid">
            @foreach ($items as $item)
                <article class="fashion-card fashion-product-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <div class="fashion-product-specs">
                        <span>
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-minimal-fashion::sections.features.default_meta'))) }}
                        </span>
                        <span>
                            {{ data_get($item, 'care_note', __('capell-theme-minimal-fashion::sections.features.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
