@php
    $heading = data_get($section, 'heading', __('capell-theme-design-led-magazine::sections.picks.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-design-led-magazine::sections.picks.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-design-led-magazine::sections.picks.home_title'), 'summary' => __('capell-theme-design-led-magazine::sections.picks.home_summary'), 'meta' => __('capell-theme-design-led-magazine::sections.picks.home_meta')],
        ['title' => __('capell-theme-design-led-magazine::sections.picks.chair_title'), 'summary' => __('capell-theme-design-led-magazine::sections.picks.chair_summary'), 'meta' => __('capell-theme-design-led-magazine::sections.picks.chair_meta')],
        ['title' => __('capell-theme-design-led-magazine::sections.picks.gallery_title'), 'summary' => __('capell-theme-design-led-magazine::sections.picks.gallery_summary'), 'meta' => __('capell-theme-design-led-magazine::sections.picks.gallery_meta')],
    ]);
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-design-led-magazine::sections.picks.kicker') }}
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
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-design-led-magazine::sections.picks.default_meta'))) }}
                        </span>
                        <span>
                            {{ data_get($item, 'care_note', __('capell-theme-design-led-magazine::sections.picks.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
