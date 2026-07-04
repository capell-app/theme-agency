@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-scoreboard-showcase::sections.footer.awards'), 'links' => [
            ['label' => __('capell-theme-scoreboard-showcase::sections.footer.winner_of_day'), 'url' => '/'],
            ['label' => __('capell-theme-scoreboard-showcase::sections.footer.newest_nominees'), 'url' => '/'],
            ['label' => __('capell-theme-scoreboard-showcase::sections.footer.previous_winners'), 'url' => '/'],
        ]],
        ['title' => __('capell-theme-scoreboard-showcase::sections.footer.participate'), 'links' => [
            ['label' => __('capell-theme-scoreboard-showcase::sections.footer.submit_work'), 'url' => '/'],
            ['label' => __('capell-theme-scoreboard-showcase::sections.footer.cast_vote'), 'url' => '/'],
        ]],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="sbs-section sbs-section-dark">
    <div class="sbs-section-inner">
        <div class="sbs-footer-grid">
            @if (filled($brand))
                <div class="sbs-footer-brand">
                    <p class="sbs-footer-wordmark">{{ $brand }}</p>
                    @if (filled($brandSummary))
                        <p class="sbs-footer-summary">{{ $brandSummary }}</p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="sbs-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="sbs-footer-links">
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
