@php
    $columns = data_get($section, 'items', data_get($section, 'columns', []));
    $brand = data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-first-light::sections.navigation.brand')));
    $brandSummary = data_get($section, 'summary');
@endphp

<footer class="mcf-footer">
    <div class="mcf-section-inner">
        <div class="mcf-footer-grid">
            <div class="mcf-footer-brand">
                <p class="mcf-footer-wordmark">{{ $brand }}</p>
                @if (filled($brandSummary))
                    <p class="mcf-footer-summary">{{ $brandSummary }}</p>
                @endif
            </div>

            @foreach ($columns as $column)
                <section>
                    <h3 class="mcf-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="mcf-footer-links">
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
