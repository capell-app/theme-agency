@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        [
            'heading' => __('capell-theme-deep-bench::sections.footer.browse'),
            'links' => [
                __('capell-theme-deep-bench::sections.footer.directory'),
                __('capell-theme-deep-bench::sections.footer.disciplines'),
                __('capell-theme-deep-bench::sections.footer.lists'),
            ],
        ],
        [
            'heading' => __('capell-theme-deep-bench::sections.footer.creators'),
            'links' => [
                __('capell-theme-deep-bench::sections.footer.submit'),
                __('capell-theme-deep-bench::sections.footer.resources'),
                __('capell-theme-deep-bench::sections.footer.journal'),
            ],
        ],
        [
            'heading' => __('capell-theme-deep-bench::sections.footer.stay'),
            'links' => [
                __('capell-theme-deep-bench::sections.footer.newsletter'),
            ],
        ],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary', __('capell-theme-deep-bench::sections.footer.summary'));
@endphp

<footer class="pfd-section pfd-section-night">
    <div class="pfd-section-inner">
        <div class="pfd-footer-grid">
            @if (filled($brand))
                <div class="pfd-footer-brand">
                    <p class="pfd-footer-wordmark">
                        <span
                            class="pfd-brand-mark"
                            aria-hidden="true"
                        ></span>
                        {{ $brand }}
                    </p>
                    @if (filled($brandSummary))
                        <p class="pfd-footer-summary">{{ $brandSummary }}</p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="pfd-footer-heading">
                        {{ data_get($column, 'heading', data_get($column, 'title', '')) }}
                    </h3>
                    <ul class="pfd-footer-links">
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
