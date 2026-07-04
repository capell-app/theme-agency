@php
    $brand = data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-filter-gallery::sections.footer.brand')));
    $summary = data_get($section, 'summary', __('capell-theme-filter-gallery::sections.footer.summary'));
    $fallbackColumns = __('capell-theme-filter-gallery::sections.footer.columns');
    $columns = collect(data_get($section, 'items', data_get($section, 'columns', is_array($fallbackColumns) ? $fallbackColumns : [])))
        ->filter(fn (mixed $column): bool => filled(data_get($column, 'title', data_get($column, 'heading'))))
        ->values();
@endphp

<footer class="fga-section fga-section-dark">
    <div class="fga-section-inner">
        <div class="fga-footer-grid">
            <div class="fga-footer-brand">
                <span class="fga-footer-wordmark">{{ $brand }}</span>
                <p class="fga-footer-summary">{{ $summary }}</p>
                <p class="fga-mono-note">
                    {{ __('capell-theme-filter-gallery::sections.navigation.index_count') }}
                </p>
            </div>

            @foreach ($columns as $column)
                <nav
                    aria-label="{{ data_get($column, 'title', data_get($column, 'heading', '')) }}"
                >
                    <h3 class="fga-footer-heading">
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul class="fga-footer-links">
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
                </nav>
            @endforeach
        </div>

        <p class="fga-footer-colophon">
            {{ __('capell-theme-filter-gallery::sections.footer.colophon') }}
        </p>
    </div>
</footer>
