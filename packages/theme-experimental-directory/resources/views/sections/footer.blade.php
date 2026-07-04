@php
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-experimental-directory::sections.footer.index'), 'links' => [
            ['label' => __('capell-theme-experimental-directory::sections.footer.today'), 'url' => '/'],
            ['label' => __('capell-theme-experimental-directory::sections.footer.submissions'), 'url' => '/theme-experimental-directory-directory'],
            ['label' => __('capell-theme-experimental-directory::sections.footer.winners'), 'url' => '/theme-experimental-directory-cta'],
        ]],
        ['title' => __('capell-theme-experimental-directory::sections.footer.studios'), 'links' => [
            ['label' => __('capell-theme-experimental-directory::sections.footer.profiles'), 'url' => '/theme-experimental-directory-detail'],
            ['label' => __('capell-theme-experimental-directory::sections.footer.submit'), 'url' => '/theme-experimental-directory-contact'],
        ]],
    ]));
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="exd-section exd-section-raised">
    <div class="exd-section-inner">
        <div class="exd-footer-grid">
            @if (filled($brand))
                <div class="exd-footer-brand">
                    <p class="exd-footer-wordmark">{{ $brand }}</p>
                    @if (filled($brandSummary))
                        <p class="exd-footer-summary">
                            {{ $brandSummary }}
                        </p>
                    @endif
                </div>
            @endif

            @foreach ($columns as $column)
                <section>
                    <h3 class="exd-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="exd-footer-links">
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
