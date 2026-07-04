@php
    $brand = data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-global-culture-magazine::sections.navigation.brand')));
    $summary = data_get($section, 'summary', '');
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-global-culture-magazine::sections.footer.departments'), 'links' => [__('capell-theme-global-culture-magazine::sections.footer.affairs'), __('capell-theme-global-culture-magazine::sections.footer.travel'), __('capell-theme-global-culture-magazine::sections.footer.culture')]],
        ['title' => __('capell-theme-global-culture-magazine::sections.footer.formats'), 'links' => [__('capell-theme-global-culture-magazine::sections.footer.radio'), __('capell-theme-global-culture-magazine::sections.footer.city_guides'), __('capell-theme-global-culture-magazine::sections.footer.books')]],
        ['title' => __('capell-theme-global-culture-magazine::sections.footer.magazine'), 'links' => [__('capell-theme-global-culture-magazine::sections.footer.columnists'), __('capell-theme-global-culture-magazine::sections.footer.shop'), __('capell-theme-global-culture-magazine::sections.footer.newsletter')]],
    ]));
@endphp

<footer class="gcm-section gcm-section-dark">
    <div class="gcm-section-inner">
        <div class="gcm-footer-brand">
            <strong>{{ $brand }}</strong>
            @if ($summary !== '')
                <p>{{ $summary }}</p>
            @endif
        </div>

        <div class="gcm-footer-columns">
            @foreach ($columns as $column)
                <section>
                    <h3>
                        {{ data_get($column, 'title', data_get($column, 'heading', '')) }}
                    </h3>
                    <ul>
                        @foreach (data_get($column, 'links', []) as $link)
                            <li>
                                @if (is_array($link))
                                    <a
                                        href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                                    >
                                        {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                                    </a>
                                @else
                                    <span>{{ $link }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        <p class="gcm-footer-rule">
            {{ __('capell-theme-global-culture-magazine::sections.footer.colophon') }}
        </p>
    </div>
</footer>
