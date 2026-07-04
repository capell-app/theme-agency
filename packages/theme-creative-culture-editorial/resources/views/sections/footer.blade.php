@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-creative-culture-editorial::sections.footer.editorial'), 'links' => [['label' => __('capell-theme-creative-culture-editorial::sections.footer.stories'), 'url' => '/#project-stories'], ['label' => __('capell-theme-creative-culture-editorial::sections.footer.updates'), 'url' => '/#opinion-block'], ['label' => __('capell-theme-creative-culture-editorial::sections.footer.newsletter'), 'url' => '/#newsletter']]],
        ['title' => __('capell-theme-creative-culture-editorial::sections.footer.service'), 'links' => [['label' => __('capell-theme-creative-culture-editorial::sections.footer.support'), 'url' => '/#advice-culture'], ['label' => __('capell-theme-creative-culture-editorial::sections.footer.trade_in'), 'url' => '/#events-tags'], ['label' => __('capell-theme-creative-culture-editorial::sections.footer.financing'), 'url' => '/theme-creative-culture-editorial-directory']]],
        ['title' => __('capell-theme-creative-culture-editorial::sections.footer.shop'), 'links' => [['label' => __('capell-theme-creative-culture-editorial::sections.footer.models'), 'url' => '/theme-creative-culture-editorial-directory'], ['label' => __('capell-theme-creative-culture-editorial::sections.footer.accessories'), 'url' => '/#events-tags']]],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="cce-section cce-section-dark">
    <div class="cce-section-inner">
        <div class="cce-footer-grid">
            @if (filled($brand))
                <div class="cce-footer-brand">
                    <p class="cce-footer-wordmark">{{ $brand }}</p>
                    @if (filled($brandSummary))
                        <p class="cce-footer-summary">{{ $brandSummary }}</p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="cce-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="cce-footer-links">
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
