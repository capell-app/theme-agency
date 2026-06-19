@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-design-led-magazine::sections.categories.architecture_title'), 'summary' => __('capell-theme-design-led-magazine::sections.categories.architecture_summary')],
        ['title' => __('capell-theme-design-led-magazine::sections.categories.interiors_title'), 'summary' => __('capell-theme-design-led-magazine::sections.categories.interiors_summary')],
        ['title' => __('capell-theme-design-led-magazine::sections.categories.fashion_title'), 'summary' => __('capell-theme-design-led-magazine::sections.categories.fashion_summary')],
        ['title' => __('capell-theme-design-led-magazine::sections.categories.art_title'), 'summary' => __('capell-theme-design-led-magazine::sections.categories.art_summary')],
    ]);
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-design-led-magazine::sections.categories.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-design-led-magazine::sections.categories.heading')) }}
        </h2>
        <div class="editorial-grid">
            @foreach ($items as $item)
                <article class="editorial-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
