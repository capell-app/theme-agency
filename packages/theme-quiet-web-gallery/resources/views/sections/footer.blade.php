@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-quiet-web-gallery::sections.footer.browse'), 'links' => [['label' => __('capell-theme-quiet-web-gallery::sections.navigation.browse'), 'url' => '#browse-panels'], ['label' => __('capell-theme-quiet-web-gallery::sections.navigation.latest'), 'url' => '#latest-showcase']]],
        ['title' => __('capell-theme-quiet-web-gallery::sections.footer.discover'), 'links' => [['label' => __('capell-theme-quiet-web-gallery::sections.navigation.categories'), 'url' => '#style-type-categories'], ['label' => __('capell-theme-quiet-web-gallery::sections.navigation.best_of'), 'url' => '#random-best-of']]],
        ['title' => __('capell-theme-quiet-web-gallery::sections.footer.join'), 'links' => [['label' => __('capell-theme-quiet-web-gallery::sections.footer.newsletter'), 'url' => '#newsletter']]],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="qwg-section qwg-section-dark">
    <div class="qwg-section-inner">
        <div class="qwg-footer-grid">
            @if (filled($brand))
                <div class="qwg-footer-brand">
                    <p class="qwg-footer-wordmark">{{ $brand }}</p>
                    @if (filled($brandSummary))
                        <p class="qwg-footer-summary">
                            {{ $brandSummary }}
                        </p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="qwg-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="qwg-footer-links">
                        @foreach (data_get($column, 'links', []) as $link)
                            <li>
                                @if (is_array($link) && filled(data_get($link, 'url', data_get($link, 'href'))))
                                    <a
                                        href="{{ data_get($link, 'url', data_get($link, 'href')) }}"
                                    >
                                        {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                                    </a>
                                @else
                                    {{ is_array($link) ? data_get($link, 'label', data_get($link, 'title', '')) : $link }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </div>
</footer>
