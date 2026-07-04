@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-one-page-showcase::sections.events.planning_title'), 'summary' => __('capell-theme-one-page-showcase::sections.events.planning_summary'), 'meta' => __('capell-theme-one-page-showcase::sections.events.planning_meta')],
        ['title' => __('capell-theme-one-page-showcase::sections.events.workshop_title'), 'summary' => __('capell-theme-one-page-showcase::sections.events.workshop_summary'), 'meta' => __('capell-theme-one-page-showcase::sections.events.workshop_meta')],
        ['title' => __('capell-theme-one-page-showcase::sections.events.systems_title'), 'summary' => __('capell-theme-one-page-showcase::sections.events.systems_summary'), 'meta' => __('capell-theme-one-page-showcase::sections.events.systems_meta')],
    ]));
@endphp

<section
    id="tools-sponsors"
    class="ops-section"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-page-showcase::sections.events.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-one-page-showcase::sections.events.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="ops-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="ops-grid">
            @foreach ($stories as $story)
                <article class="ops-card">
                    <p class="ops-meta">
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
