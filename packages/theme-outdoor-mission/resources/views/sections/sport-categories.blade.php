@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-outdoor-mission::sections.sports.climb'), 'summary' => __('capell-theme-outdoor-mission::sections.sports.climb_summary')],
        ['title' => __('capell-theme-outdoor-mission::sections.sports.trail'), 'summary' => __('capell-theme-outdoor-mission::sections.sports.trail_summary')],
        ['title' => __('capell-theme-outdoor-mission::sections.sports.surf'), 'summary' => __('capell-theme-outdoor-mission::sections.sports.surf_summary')],
        ['title' => __('capell-theme-outdoor-mission::sections.sports.snow'), 'summary' => __('capell-theme-outdoor-mission::sections.sports.snow_summary')],
    ]);
@endphp

<section class="outdoor-section">
    <div class="outdoor-section-inner">
        <p class="outdoor-kicker">
            {{ __('capell-theme-outdoor-mission::sections.sports.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-outdoor-mission::sections.sports.heading')) }}
        </h2>
        <div class="outdoor-grid">
            @foreach ($items as $item)
                <article class="outdoor-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
