@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-creative-culture-editorial::sections.topics.product_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.topics.product_summary')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.topics.design_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.topics.design_summary')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.topics.advice_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.topics.advice_summary')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.topics.culture_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.topics.culture_summary')],
    ]);
@endphp

<section
    id="discipline-browsing"
    class="cce-section cce-section-field"
>
    <div class="cce-section-inner">
        <p class="cce-kicker">
            {{ __('capell-theme-creative-culture-editorial::sections.topics.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-creative-culture-editorial::sections.topics.heading')) }}
        </h2>
        <p class="cce-lede">
            {{ data_get($section, 'summary', __('capell-theme-creative-culture-editorial::sections.topics.summary')) }}
        </p>
        <div class="cce-grid cce-grid-tight">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="cce-card">
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="cce-title-link"
                                href="{{ $itemUrl }}"
                            >
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        @endif
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
