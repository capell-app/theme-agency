@php
    $columns = data_get($section, 'columns', [
        [
            'heading' => __('capell-theme-landing-gallery::sections.footer.gallery_heading'),
            'links' => [
                ['label' => __('capell-theme-landing-gallery::sections.footer.latest_pages'), 'url' => '/'],
                ['label' => __('capell-theme-landing-gallery::sections.footer.categories'), 'url' => '/'],
                ['label' => __('capell-theme-landing-gallery::sections.footer.partners'), 'url' => '/'],
            ],
        ],
        [
            'heading' => __('capell-theme-landing-gallery::sections.footer.marketplace_heading'),
            'links' => [
                ['label' => __('capell-theme-landing-gallery::sections.footer.paid_templates'), 'url' => '/'],
                ['label' => __('capell-theme-landing-gallery::sections.footer.submit_page'), 'url' => '/'],
                ['label' => __('capell-theme-landing-gallery::sections.footer.go_pro'), 'url' => '/'],
            ],
        ],
        [
            'heading' => __('capell-theme-landing-gallery::sections.footer.brand_heading'),
            'links' => [
                ['label' => __('capell-theme-landing-gallery::sections.footer.weekly_digest'), 'url' => '/'],
                ['label' => __('capell-theme-landing-gallery::sections.footer.contact_email'), 'url' => 'mailto:studio@galleria.example'],
            ],
        ],
    ]);
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="lga-section lga-section-dark">
    <div class="lga-section-inner">
        <div class="lga-footer-grid">
            @if (filled($brand))
                <div class="lga-footer-brand">
                    <span class="lga-footer-wordmark">
                        <span
                            class="lga-brand-mark"
                            aria-hidden="true"
                        ></span>
                        {{ $brand }}
                    </span>
                    @if (filled($brandSummary))
                        <p class="lga-footer-summary">
                            {{ $brandSummary }}
                        </p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="lga-footer-heading">
                        {{ data_get($column, 'heading', data_get($column, 'title', '')) }}
                    </h3>
                    <ul class="lga-footer-links">
                        @foreach (data_get($column, 'links', []) as $link)
                            <li>
                                @if (is_array($link))
                                    <a
                                        href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                                    >
                                        {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                                    </a>
                                @else
                                    {{ $link }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </div>
</footer>
