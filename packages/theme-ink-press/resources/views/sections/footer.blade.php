@php
    $brand = data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-ink-press::sections.navigation.brand')));
    $summary = data_get($section, 'summary', __('capell-theme-ink-press::sections.footer.summary'));
    $columns = data_get($section, 'items', data_get($section, 'columns', [
        ['title' => __('capell-theme-ink-press::sections.footer.shop'), 'links' => [__('capell-theme-ink-press::sections.footer.models'), __('capell-theme-ink-press::sections.footer.accessories'), __('capell-theme-ink-press::sections.footer.compare')]],
        ['title' => __('capell-theme-ink-press::sections.footer.service'), 'links' => [__('capell-theme-ink-press::sections.footer.support'), __('capell-theme-ink-press::sections.footer.trade_in'), __('capell-theme-ink-press::sections.footer.financing')]],
        ['title' => __('capell-theme-ink-press::sections.footer.editorial'), 'links' => [__('capell-theme-ink-press::sections.footer.stories'), __('capell-theme-ink-press::sections.footer.updates'), __('capell-theme-ink-press::sections.footer.newsletter')]],
    ]));
@endphp

<footer class="dnews-section dnews-section-dark">
    <div class="dnews-section-inner">
        <div class="dnews-footer-brand">
            <strong>{{ $brand }}</strong>
            <p class="dnews-lede">{{ $summary }}</p>
        </div>

        <div class="dnews-footer-columns">
            @foreach ($columns as $column)
                <section>
                    <h3>
                        {{ data_get($column, 'heading', data_get($column, 'title', '')) }}
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
                                    <a href="/">{{ $link }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        <p class="dnews-footer-fine dnews-meta">
            <span>
                {{ $brand }} · {{ __('capell-theme-ink-press::sections.footer.fine_print') }}
            </span>
            <span class="dnews-masthead-live">
                {{ __('capell-theme-ink-press::sections.navigation.live') }}
            </span>
        </p>
    </div>
</footer>
