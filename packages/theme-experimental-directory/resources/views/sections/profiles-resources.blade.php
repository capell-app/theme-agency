@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-experimental-directory::sections.events.planning_title'), 'summary' => __('capell-theme-experimental-directory::sections.events.planning_summary')],
        ['title' => __('capell-theme-experimental-directory::sections.events.workshop_title'), 'summary' => __('capell-theme-experimental-directory::sections.events.workshop_summary')],
        ['title' => __('capell-theme-experimental-directory::sections.events.systems_title'), 'summary' => __('capell-theme-experimental-directory::sections.events.systems_summary')],
    ]));
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-experimental-directory::sections.events.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-experimental-directory::sections.events.heading')) }}
        </h2>
        <div class="editorial-grid">
            @foreach ($stories as $story)
                <article class="editorial-card">
                    <p class="editorial-meta">
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
