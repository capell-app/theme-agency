@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-minimal-fashion::sections.categories.women'), 'summary' => __('capell-theme-minimal-fashion::sections.categories.women_summary')],
        ['title' => __('capell-theme-minimal-fashion::sections.categories.men'), 'summary' => __('capell-theme-minimal-fashion::sections.categories.men_summary')],
        ['title' => __('capell-theme-minimal-fashion::sections.categories.objects'), 'summary' => __('capell-theme-minimal-fashion::sections.categories.objects_summary')],
        ['title' => __('capell-theme-minimal-fashion::sections.categories.stores'), 'summary' => __('capell-theme-minimal-fashion::sections.categories.stores_summary')],
    ]);
@endphp

<section class="fashion-section">
    <div class="fashion-section-inner">
        <p class="fashion-kicker">
            {{ __('capell-theme-minimal-fashion::sections.categories.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-minimal-fashion::sections.categories.heading')) }}
        </h2>
        <div class="fashion-grid">
            @foreach ($items as $item)
                <article class="fashion-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
