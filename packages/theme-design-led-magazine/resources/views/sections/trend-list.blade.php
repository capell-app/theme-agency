@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-design-led-magazine::sections.trends.palette_title'), 'summary' => __('capell-theme-design-led-magazine::sections.trends.palette_summary')],
        ['title' => __('capell-theme-design-led-magazine::sections.trends.rooms_title'), 'summary' => __('capell-theme-design-led-magazine::sections.trends.rooms_summary')],
        ['title' => __('capell-theme-design-led-magazine::sections.trends.objects_title'), 'summary' => __('capell-theme-design-led-magazine::sections.trends.objects_summary')],
    ]);
@endphp

<section
    id="trend-list"
    class="dlm-section dlm-section-field"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-design-led-magazine::sections.trends.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-design-led-magazine::sections.trends.heading')) }}
        </h2>
        <p class="dlm-lede">
            {{ data_get($section, 'summary', __('capell-theme-design-led-magazine::sections.trends.summary')) }}
        </p>
        <div class="dlm-grid">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="dlm-card">
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
