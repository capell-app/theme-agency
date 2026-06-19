@php
    $heading = data_get($section, 'heading', __('capell-theme-bold-sport-commerce::sections.features.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-bold-sport-commerce::sections.features.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-bold-sport-commerce::sections.features.shoe_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.features.shoe_summary'), 'meta' => __('capell-theme-bold-sport-commerce::sections.features.shoe_meta')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.features.jacket_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.features.jacket_summary'), 'meta' => __('capell-theme-bold-sport-commerce::sections.features.jacket_meta')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.features.kit_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.features.kit_summary'), 'meta' => __('capell-theme-bold-sport-commerce::sections.features.kit_meta')],
    ]);
@endphp

<section class="sport-section">
    <div class="sport-section-inner">
        <p class="sport-kicker">
            {{ __('capell-theme-bold-sport-commerce::sections.features.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="sport-lede">{{ $summary }}</p>

        <div class="sport-grid">
            @foreach ($items as $item)
                <article class="sport-card sport-product-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <div class="sport-product-specs">
                        <span>
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-bold-sport-commerce::sections.features.default_meta'))) }}
                        </span>
                        <span>
                            {{ data_get($item, 'care_note', __('capell-theme-bold-sport-commerce::sections.features.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
