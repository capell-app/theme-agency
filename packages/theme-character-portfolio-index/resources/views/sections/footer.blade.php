@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-character-portfolio-index::sections.footer.the_index'), 'links' => [__('capell-theme-character-portfolio-index::sections.footer.browse_portfolios'), __('capell-theme-character-portfolio-index::sections.footer.categories'), __('capell-theme-character-portfolio-index::sections.footer.standout_notes')]],
        ['title' => __('capell-theme-character-portfolio-index::sections.footer.take_part'), 'links' => [__('capell-theme-character-portfolio-index::sections.footer.submit_portfolio'), __('capell-theme-character-portfolio-index::sections.footer.nominate_maker'), __('capell-theme-character-portfolio-index::sections.footer.weekly_digest')]],
        ['title' => __('capell-theme-character-portfolio-index::sections.footer.connect'), 'links' => [__('capell-theme-character-portfolio-index::sections.footer.newsletter'), __('capell-theme-character-portfolio-index::sections.footer.latest_issue')]],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="cpi-section cpi-section-dark">
    <div class="cpi-section-inner">
        <div class="cpi-footer-grid">
            @if (filled($brand))
                <div class="cpi-footer-brand">
                    <p class="cpi-footer-wordmark">{{ $brand }}</p>
                    @if (filled($brandSummary))
                        <p class="cpi-footer-summary">
                            {{ $brandSummary }}
                        </p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="cpi-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="cpi-footer-links">
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
