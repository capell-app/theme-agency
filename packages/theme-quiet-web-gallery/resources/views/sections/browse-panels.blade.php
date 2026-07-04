@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-web-gallery::sections.panels.style_title'), 'summary' => __('capell-theme-quiet-web-gallery::sections.panels.style_summary'), 'url' => '#style-type-categories'],
        ['title' => __('capell-theme-quiet-web-gallery::sections.panels.type_title'), 'summary' => __('capell-theme-quiet-web-gallery::sections.panels.type_summary'), 'url' => '#style-type-categories'],
        ['title' => __('capell-theme-quiet-web-gallery::sections.panels.random_title'), 'summary' => __('capell-theme-quiet-web-gallery::sections.panels.random_summary'), 'url' => '#random-best-of'],
    ]);
@endphp

<section
    id="browse-panels"
    class="qwg-section"
>
    <div class="qwg-section-inner">
        <p class="qwg-kicker">
            {{ __('capell-theme-quiet-web-gallery::sections.panels.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-web-gallery::sections.panels.heading')) }}
        </h2>
        <p class="qwg-lede">
            {{ data_get($section, 'summary', __('capell-theme-quiet-web-gallery::sections.panels.summary')) }}
        </p>

        <div class="qwg-panels">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                @endphp

                <article class="qwg-panel">
                    <span
                        class="qwg-panel-index"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="qwg-title-link"
                                href="{{ $itemUrl }}"
                            >
                                {{ $itemTitle }}
                            </a>
                        @else
                            {{ $itemTitle }}
                        @endif
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
