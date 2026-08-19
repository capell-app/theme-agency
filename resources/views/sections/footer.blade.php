@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-agency::sections.footer.browse_heading'), 'links' => [__('capell-theme-agency::sections.footer.models'), __('capell-theme-agency::sections.footer.accessories'), __('capell-theme-agency::sections.footer.compare')]],
        ['title' => __('capell-theme-agency::sections.footer.members_heading'), 'links' => [__('capell-theme-agency::sections.footer.support'), __('capell-theme-agency::sections.footer.trade_in'), __('capell-theme-agency::sections.footer.financing')]],
        ['title' => __('capell-theme-agency::sections.footer.brand_heading'), 'links' => [__('capell-theme-agency::sections.footer.stories'), __('capell-theme-agency::sections.footer.updates'), __('capell-theme-agency::sections.footer.newsletter')]],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="ppc-section ppc-section-dark">
    <div class="ppc-section-inner">
        <div class="ppc-footer-grid">
            @if (filled($brand))
                <div class="ppc-footer-brand">
                    <p class="ppc-footer-wordmark">{{ $brand }}</p>
                    @if (filled($brandSummary))
                        <p class="ppc-footer-summary">{{ $brandSummary }}</p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="ppc-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="ppc-footer-links">
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

    <x-capell::layout.area area="footer" />
</footer>
