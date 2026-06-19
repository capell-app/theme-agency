@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-global-culture-magazine::sections.shop.lighting_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.shop.lighting_summary')],
        ['title' => __('capell-theme-global-culture-magazine::sections.shop.materials_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.shop.materials_summary')],
        ['title' => __('capell-theme-global-culture-magazine::sections.shop.books_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.shop.books_summary')],
    ]));
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-global-culture-magazine::sections.shop.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-global-culture-magazine::sections.shop.heading')) }}
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
