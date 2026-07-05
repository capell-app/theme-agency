@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-night-shift::sections.footer.shop'), 'links' => [__('capell-theme-night-shift::sections.footer.models'), __('capell-theme-night-shift::sections.footer.accessories'), __('capell-theme-night-shift::sections.footer.compare')]],
        ['title' => __('capell-theme-night-shift::sections.footer.service'), 'links' => [__('capell-theme-night-shift::sections.footer.support'), __('capell-theme-night-shift::sections.footer.trade_in'), __('capell-theme-night-shift::sections.footer.financing')]],
        ['title' => __('capell-theme-night-shift::sections.footer.editorial'), 'links' => [__('capell-theme-night-shift::sections.footer.stories'), __('capell-theme-night-shift::sections.footer.updates'), __('capell-theme-night-shift::sections.footer.newsletter')]],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer
    id="footer"
    class="dps-section dps-footer"
>
    <div class="dps-section-inner">
        <div class="dps-footer-grid">
            @if (filled($brand))
                <div class="dps-footer-brand">
                    <p class="dps-footer-wordmark">{{ $brand }}</p>
                    @if (filled($brandSummary))
                        <p class="dps-footer-summary">{{ $brandSummary }}</p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="dps-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="dps-footer-links">
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

        <div class="dps-footer-base">
            <span>
                {{ __('capell-theme-night-shift::sections.footer.base_note') }}
            </span>
        </div>
    </div>
</footer>
