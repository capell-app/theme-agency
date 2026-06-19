@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-product-company-editorial::sections.templates.planning_title'), 'summary' => __('capell-theme-product-company-editorial::sections.templates.planning_summary')],
        ['title' => __('capell-theme-product-company-editorial::sections.templates.workshop_title'), 'summary' => __('capell-theme-product-company-editorial::sections.templates.workshop_summary')],
        ['title' => __('capell-theme-product-company-editorial::sections.templates.systems_title'), 'summary' => __('capell-theme-product-company-editorial::sections.templates.systems_summary')],
    ]));
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-product-company-editorial::sections.templates.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-product-company-editorial::sections.templates.heading')) }}
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
