@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-raw-index::sections.footer.wall_column'), 'links' => [
            ['label' => __('capell-theme-raw-index::sections.footer.wall'), 'url' => '/'],
            ['label' => __('capell-theme-raw-index::sections.footer.interviews'), 'url' => '/'],
            ['label' => __('capell-theme-raw-index::sections.footer.newsletter'), 'url' => '/'],
        ]],
        ['title' => __('capell-theme-raw-index::sections.footer.archive_column'), 'links' => [
            ['label' => __('capell-theme-raw-index::sections.footer.by_year'), 'url' => '/'],
            ['label' => __('capell-theme-raw-index::sections.footer.language_notes'), 'url' => '/'],
            ['label' => __('capell-theme-raw-index::sections.footer.corrections'), 'url' => '/'],
        ]],
        ['title' => __('capell-theme-raw-index::sections.footer.submit_column'), 'links' => [
            ['label' => __('capell-theme-raw-index::sections.footer.submit_scan'), 'url' => '/'],
            ['label' => __('capell-theme-raw-index::sections.footer.rss'), 'url' => '/'],
        ]],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="rwi-section rwi-section-dark">
    <div class="rwi-section-inner">
        <div class="rwi-footer-grid">
            @if (filled($brand))
                <div class="rwi-footer-brand">
                    <p class="rwi-footer-wordmark">{{ $brand }}</p>
                    @if (filled($brandSummary))
                        <p class="rwi-footer-summary">{{ $brandSummary }}</p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="rwi-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="rwi-footer-links">
                        @foreach (data_get($column, 'links', []) as $link)
                            <li>
                                @if (is_array($link) && filled(data_get($link, 'url', data_get($link, 'href'))))
                                    <a
                                        href="{{ data_get($link, 'url', data_get($link, 'href')) }}"
                                    >
                                        {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                                    </a>
                                @else
                                    <span>
                                        {{ is_array($link) ? data_get($link, 'label', data_get($link, 'title', '')) : $link }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    </div>
</footer>
