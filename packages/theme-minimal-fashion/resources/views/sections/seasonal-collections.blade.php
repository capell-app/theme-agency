@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-minimal-fashion::sections.seasonal.outerwear_title'), 'summary' => __('capell-theme-minimal-fashion::sections.seasonal.outerwear_summary')],
        ['title' => __('capell-theme-minimal-fashion::sections.seasonal.knitwear_title'), 'summary' => __('capell-theme-minimal-fashion::sections.seasonal.knitwear_summary')],
        ['title' => __('capell-theme-minimal-fashion::sections.seasonal.essentials_title'), 'summary' => __('capell-theme-minimal-fashion::sections.seasonal.essentials_summary')],
    ]);
@endphp

<section class="fashion-section">
    <div class="fashion-section-inner fashion-split">
        <div>
            <p class="fashion-kicker">
                {{ __('capell-theme-minimal-fashion::sections.seasonal.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-minimal-fashion::sections.seasonal.heading')) }}
            </h2>
            <p class="fashion-lede">
                {{ data_get($section, 'summary', __('capell-theme-minimal-fashion::sections.seasonal.summary')) }}
            </p>
        </div>
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
