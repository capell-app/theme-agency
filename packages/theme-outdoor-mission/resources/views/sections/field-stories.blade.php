@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-outdoor-mission::sections.stories.river_title'), 'summary' => __('capell-theme-outdoor-mission::sections.stories.river_summary'), 'meta' => __('capell-theme-outdoor-mission::sections.stories.river_meta')],
        ['title' => __('capell-theme-outdoor-mission::sections.stories.alpine_title'), 'summary' => __('capell-theme-outdoor-mission::sections.stories.alpine_summary'), 'meta' => __('capell-theme-outdoor-mission::sections.stories.alpine_meta')],
        ['title' => __('capell-theme-outdoor-mission::sections.stories.repair_title'), 'summary' => __('capell-theme-outdoor-mission::sections.stories.repair_summary'), 'meta' => __('capell-theme-outdoor-mission::sections.stories.repair_meta')],
    ]));
@endphp

<section class="outdoor-section">
    <div class="outdoor-section-inner">
        <p class="outdoor-kicker">
            {{ __('capell-theme-outdoor-mission::sections.stories.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-outdoor-mission::sections.stories.heading')) }}
        </h2>
        <div class="outdoor-grid">
            @foreach ($stories as $story)
                <article class="outdoor-card">
                    <p class="outdoor-meta">
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
