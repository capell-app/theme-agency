@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-outdoor-mission::sections.seasonal.rain_title'), 'summary' => __('capell-theme-outdoor-mission::sections.seasonal.rain_summary')],
        ['title' => __('capell-theme-outdoor-mission::sections.seasonal.heat_title'), 'summary' => __('capell-theme-outdoor-mission::sections.seasonal.heat_summary')],
        ['title' => __('capell-theme-outdoor-mission::sections.seasonal.camp_title'), 'summary' => __('capell-theme-outdoor-mission::sections.seasonal.camp_summary')],
    ]);
@endphp

<section class="outdoor-section">
    <div class="outdoor-section-inner outdoor-split">
        <div>
            <p class="outdoor-kicker">
                {{ __('capell-theme-outdoor-mission::sections.seasonal.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-outdoor-mission::sections.seasonal.heading')) }}
            </h2>
            <p class="outdoor-lede">
                {{ data_get($section, 'summary', __('capell-theme-outdoor-mission::sections.seasonal.summary')) }}
            </p>
        </div>
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
