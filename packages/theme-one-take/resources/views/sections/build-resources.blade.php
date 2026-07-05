@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-one-take::sections.authors.design_title'), 'summary' => __('capell-theme-one-take::sections.authors.design_summary')],
        ['title' => __('capell-theme-one-take::sections.authors.advice_title'), 'summary' => __('capell-theme-one-take::sections.authors.advice_summary')],
        ['title' => __('capell-theme-one-take::sections.authors.company_title'), 'summary' => __('capell-theme-one-take::sections.authors.company_summary')],
    ]);
@endphp

<section
    id="build-resources"
    class="ops-section ops-section-field"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-take::sections.authors.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-one-take::sections.authors.heading')) }}
        </h2>
        <p class="ops-lede">
            {{ data_get($section, 'summary', __('capell-theme-one-take::sections.authors.summary')) }}
        </p>
        <div class="ops-index">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="ops-index-row">
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="ops-title-link"
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
