@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-design-led-magazine::sections.categories.architecture_title'), 'summary' => __('capell-theme-design-led-magazine::sections.categories.architecture_summary')],
        ['title' => __('capell-theme-design-led-magazine::sections.categories.interiors_title'), 'summary' => __('capell-theme-design-led-magazine::sections.categories.interiors_summary')],
        ['title' => __('capell-theme-design-led-magazine::sections.categories.fashion_title'), 'summary' => __('capell-theme-design-led-magazine::sections.categories.fashion_summary')],
        ['title' => __('capell-theme-design-led-magazine::sections.categories.art_title'), 'summary' => __('capell-theme-design-led-magazine::sections.categories.art_summary')],
    ]);
@endphp

<section
    id="vertical-categories"
    class="dlm-section"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-design-led-magazine::sections.categories.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-design-led-magazine::sections.categories.heading')) }}
        </h2>
        <div class="dlm-index">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="dlm-index-row">
                    <span
                        class="dlm-numeral"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="dlm-title-link"
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
