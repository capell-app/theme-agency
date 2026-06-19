@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-interactive-builder::sections.topics.product_title'), 'summary' => __('capell-theme-interactive-builder::sections.topics.product_summary')],
        ['title' => __('capell-theme-interactive-builder::sections.topics.design_title'), 'summary' => __('capell-theme-interactive-builder::sections.topics.design_summary')],
        ['title' => __('capell-theme-interactive-builder::sections.topics.advice_title'), 'summary' => __('capell-theme-interactive-builder::sections.topics.advice_summary')],
        ['title' => __('capell-theme-interactive-builder::sections.topics.culture_title'), 'summary' => __('capell-theme-interactive-builder::sections.topics.culture_summary')],
    ]);
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-interactive-builder::sections.topics.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-interactive-builder::sections.topics.heading')) }}
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
