@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-bold-sport-commerce::sections.stories.runner_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.stories.runner_summary'), 'meta' => __('capell-theme-bold-sport-commerce::sections.stories.runner_meta')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.stories.team_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.stories.team_summary'), 'meta' => __('capell-theme-bold-sport-commerce::sections.stories.team_meta')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.stories.athlete_title'), 'summary' => __('capell-theme-bold-sport-commerce::sections.stories.athlete_summary'), 'meta' => __('capell-theme-bold-sport-commerce::sections.stories.athlete_meta')],
    ]));
@endphp

<section class="sport-section">
    <div class="sport-section-inner">
        <p class="sport-kicker">
            {{ __('capell-theme-bold-sport-commerce::sections.stories.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-bold-sport-commerce::sections.stories.heading')) }}
        </h2>
        <div class="sport-grid">
            @foreach ($stories as $story)
                <article class="sport-card">
                    <p class="sport-meta">
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
