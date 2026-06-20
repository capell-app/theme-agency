@php
    $heading = data_get($section, 'heading', __('capell-theme-motion-archive::sections.stories.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-motion-archive::sections.stories.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-motion-archive::sections.stories.product_title'), 'summary' => __('capell-theme-motion-archive::sections.stories.product_summary'), 'meta' => __('capell-theme-motion-archive::sections.stories.product_meta')],
        ['title' => __('capell-theme-motion-archive::sections.stories.design_title'), 'summary' => __('capell-theme-motion-archive::sections.stories.design_summary'), 'meta' => __('capell-theme-motion-archive::sections.stories.design_meta')],
        ['title' => __('capell-theme-motion-archive::sections.stories.advice_title'), 'summary' => __('capell-theme-motion-archive::sections.stories.advice_summary'), 'meta' => __('capell-theme-motion-archive::sections.stories.advice_meta')],
    ]);
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-motion-archive::sections.stories.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="editorial-lede">{{ $summary }}</p>

        <div class="editorial-grid">
            @foreach ($items as $item)
                <article class="editorial-card editorial-motion-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <div class="editorial-motion-specs">
                        <span>
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-motion-archive::sections.stories.default_meta'))) }}
                        </span>
                        <span>
                            {{ data_get($item, 'care_note', __('capell-theme-motion-archive::sections.stories.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
