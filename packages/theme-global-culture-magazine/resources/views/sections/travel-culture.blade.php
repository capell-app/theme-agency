@php
    $heading = data_get($section, 'heading', __('capell-theme-global-culture-magazine::sections.sections.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-global-culture-magazine::sections.sections.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-global-culture-magazine::sections.sections.home_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.sections.home_summary'), 'meta' => __('capell-theme-global-culture-magazine::sections.sections.home_meta')],
        ['title' => __('capell-theme-global-culture-magazine::sections.sections.chair_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.sections.chair_summary'), 'meta' => __('capell-theme-global-culture-magazine::sections.sections.chair_meta')],
        ['title' => __('capell-theme-global-culture-magazine::sections.sections.gallery_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.sections.gallery_summary'), 'meta' => __('capell-theme-global-culture-magazine::sections.sections.gallery_meta')],
    ]);
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-global-culture-magazine::sections.sections.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="editorial-lede">{{ $summary }}</p>

        <div class="editorial-grid">
            @foreach ($items as $item)
                <article class="editorial-card editorial-showcase-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <div class="editorial-showcase-specs">
                        <span>
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-global-culture-magazine::sections.sections.default_meta'))) }}
                        </span>
                        <span>
                            {{ data_get($item, 'care_note', __('capell-theme-global-culture-magazine::sections.sections.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
