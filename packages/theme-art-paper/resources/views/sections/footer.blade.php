@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-art-paper::sections.footer.sections'), 'links' => [__('capell-theme-art-paper::sections.footer.architecture'), __('capell-theme-art-paper::sections.footer.interiors'), __('capell-theme-art-paper::sections.footer.design')]],
        ['title' => __('capell-theme-art-paper::sections.footer.magazine'), 'links' => [__('capell-theme-art-paper::sections.footer.galleries'), __('capell-theme-art-paper::sections.footer.product_credits'), __('capell-theme-art-paper::sections.footer.city_guides')]],
        ['title' => __('capell-theme-art-paper::sections.footer.connect'), 'links' => [__('capell-theme-art-paper::sections.footer.stories'), __('capell-theme-art-paper::sections.footer.updates'), __('capell-theme-art-paper::sections.footer.newsletter')]],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="dlm-section dlm-section-dark">
    <div class="dlm-section-inner">
        <div class="dlm-footer-grid">
            @if (filled($brand))
                <div class="dlm-footer-brand">
                    <p class="dlm-footer-wordmark">{{ $brand }}</p>
                    @if (filled($brandSummary))
                        <p class="dlm-footer-summary">{{ $brandSummary }}</p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="dlm-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="dlm-footer-links">
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
