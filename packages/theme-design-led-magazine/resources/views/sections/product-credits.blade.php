@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-design-led-magazine::sections.credits.lighting_title'), 'summary' => __('capell-theme-design-led-magazine::sections.credits.lighting_summary'), 'meta' => __('capell-theme-design-led-magazine::sections.credits.lighting_meta')],
        ['title' => __('capell-theme-design-led-magazine::sections.credits.materials_title'), 'summary' => __('capell-theme-design-led-magazine::sections.credits.materials_summary'), 'meta' => __('capell-theme-design-led-magazine::sections.credits.materials_meta')],
        ['title' => __('capell-theme-design-led-magazine::sections.credits.books_title'), 'summary' => __('capell-theme-design-led-magazine::sections.credits.books_summary'), 'meta' => __('capell-theme-design-led-magazine::sections.credits.books_meta')],
    ]));
@endphp

<section
    id="product-credits"
    class="dlm-section"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-design-led-magazine::sections.credits.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-design-led-magazine::sections.credits.heading')) }}
        </h2>
        <div class="dlm-grid">
            @foreach ($stories as $story)
                <article class="dlm-card">
                    <p class="dlm-meta">
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
