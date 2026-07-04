@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-character-portfolio-index::sections.topics.product_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.topics.product_summary')],
        ['title' => __('capell-theme-character-portfolio-index::sections.topics.design_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.topics.design_summary')],
        ['title' => __('capell-theme-character-portfolio-index::sections.topics.advice_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.topics.advice_summary')],
        ['title' => __('capell-theme-character-portfolio-index::sections.topics.culture_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.topics.culture_summary')],
    ]);
@endphp

<section
    id="category-tabs"
    class="cpi-section"
>
    <div class="cpi-section-inner">
        <p class="cpi-kicker">
            {{ __('capell-theme-character-portfolio-index::sections.topics.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-character-portfolio-index::sections.topics.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="cpi-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="cpi-tab-strip">
            @foreach ($items as $item)
                <span class="cpi-tab">
                    <strong>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </strong>
                    <span>{{ data_get($item, 'summary', '') }}</span>
                </span>
            @endforeach
        </div>
    </div>
</section>
