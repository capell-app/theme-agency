@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-one-take::sections.footer.gallery'), 'links' => [
            ['label' => __('capell-theme-one-take::sections.footer.all_one_pagers'), 'url' => '/#one-page-grid'],
            ['label' => __('capell-theme-one-take::sections.footer.categories'), 'url' => '/#category-tabs'],
            ['label' => __('capell-theme-one-take::sections.footer.this_week'), 'url' => '/#showcase-hero'],
        ]],
        ['title' => __('capell-theme-one-take::sections.footer.build'), 'links' => [
            ['label' => __('capell-theme-one-take::sections.footer.templates'), 'url' => '/#templates-sections'],
            ['label' => __('capell-theme-one-take::sections.footer.tools'), 'url' => '/#tools-sponsors'],
            ['label' => __('capell-theme-one-take::sections.footer.resources'), 'url' => '/#build-resources'],
        ]],
        ['title' => __('capell-theme-one-take::sections.footer.connect'), 'links' => [
            ['label' => __('capell-theme-one-take::sections.footer.submit'), 'url' => '/#newsletter'],
            ['label' => __('capell-theme-one-take::sections.footer.newsletter'), 'url' => '/#newsletter'],
        ]],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="ops-section ops-section-dark">
    <div class="ops-section-inner">
        <div class="ops-footer-grid">
            @if (filled($brand))
                <div class="ops-footer-brand">
                    <p class="ops-footer-wordmark">{{ $brand }}</p>
                    @if (filled($brandSummary))
                        <p class="ops-footer-summary">{{ $brandSummary }}</p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="ops-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="ops-footer-links">
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
