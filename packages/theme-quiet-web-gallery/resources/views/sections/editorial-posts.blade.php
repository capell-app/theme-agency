@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-web-gallery::sections.journal.quiet_web_title'), 'summary' => __('capell-theme-quiet-web-gallery::sections.journal.quiet_web_summary')],
        ['title' => __('capell-theme-quiet-web-gallery::sections.journal.whitespace_title'), 'summary' => __('capell-theme-quiet-web-gallery::sections.journal.whitespace_summary')],
        ['title' => __('capell-theme-quiet-web-gallery::sections.journal.curating_title'), 'summary' => __('capell-theme-quiet-web-gallery::sections.journal.curating_summary')],
    ]);
@endphp

<section
    id="editorial-posts"
    class="qwg-section"
>
    <div class="qwg-section-inner">
        <p class="qwg-kicker">
            {{ __('capell-theme-quiet-web-gallery::sections.journal.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-web-gallery::sections.journal.heading')) }}
        </h2>
        <p class="qwg-lede">
            {{ data_get($section, 'summary', __('capell-theme-quiet-web-gallery::sections.journal.summary')) }}
        </p>

        <div class="qwg-index">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="qwg-index-row">
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="qwg-title-link"
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
