@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-bold-sport-commerce::sections.categories.men'), 'summary' => __('capell-theme-bold-sport-commerce::sections.categories.men_summary')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.categories.women'), 'summary' => __('capell-theme-bold-sport-commerce::sections.categories.women_summary')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.categories.kids'), 'summary' => __('capell-theme-bold-sport-commerce::sections.categories.kids_summary')],
        ['title' => __('capell-theme-bold-sport-commerce::sections.categories.teams'), 'summary' => __('capell-theme-bold-sport-commerce::sections.categories.teams_summary')],
    ]);
@endphp

<section class="sport-section">
    <div class="sport-section-inner">
        <p class="sport-kicker">
            {{ __('capell-theme-bold-sport-commerce::sections.categories.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-bold-sport-commerce::sections.categories.heading')) }}
        </h2>
        <div class="sport-grid">
            @foreach ($items as $item)
                <article class="sport-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
