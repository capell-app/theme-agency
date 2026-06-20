@php
    $heading = data_get($section, 'heading', __('capell-theme-raw-index::sections.stories.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-raw-index::sections.stories.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-raw-index::sections.stories.product_title'), 'summary' => __('capell-theme-raw-index::sections.stories.product_summary'), 'meta' => __('capell-theme-raw-index::sections.stories.product_meta')],
        ['title' => __('capell-theme-raw-index::sections.stories.design_title'), 'summary' => __('capell-theme-raw-index::sections.stories.design_summary'), 'meta' => __('capell-theme-raw-index::sections.stories.design_meta')],
        ['title' => __('capell-theme-raw-index::sections.stories.advice_title'), 'summary' => __('capell-theme-raw-index::sections.stories.advice_summary'), 'meta' => __('capell-theme-raw-index::sections.stories.advice_meta')],
    ]);
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-raw-index::sections.stories.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="editorial-lede">{{ $summary }}</p>

        <div class="editorial-grid">
            @foreach ($items as $item)
                <article class="editorial-card editorial-raw-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <div class="editorial-raw-specs">
                        <span>
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-raw-index::sections.stories.default_meta'))) }}
                        </span>
                        <span>
                            {{ data_get($item, 'care_note', __('capell-theme-raw-index::sections.stories.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
