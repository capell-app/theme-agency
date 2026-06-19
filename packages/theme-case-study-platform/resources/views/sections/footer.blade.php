@php
    $columns = data_get($section, 'items', [
        ['title' => __('capell-theme-case-study-platform::sections.footer.shop'), 'links' => [__('capell-theme-case-study-platform::sections.footer.models'), __('capell-theme-case-study-platform::sections.footer.accessories'), __('capell-theme-case-study-platform::sections.footer.compare')]],
        ['title' => __('capell-theme-case-study-platform::sections.footer.service'), 'links' => [__('capell-theme-case-study-platform::sections.footer.support'), __('capell-theme-case-study-platform::sections.footer.trade_in'), __('capell-theme-case-study-platform::sections.footer.financing')]],
        ['title' => __('capell-theme-case-study-platform::sections.footer.editorial'), 'links' => [__('capell-theme-case-study-platform::sections.footer.stories'), __('capell-theme-case-study-platform::sections.footer.updates'), __('capell-theme-case-study-platform::sections.footer.newsletter')]],
    ]);
@endphp

<footer class="editorial-section editorial-section-dark">
    <div class="editorial-section-inner">
        <div class="editorial-grid">
            @foreach ($columns as $column)
                <section>
                    <h3>{{ data_get($column, 'title', '') }}</h3>
                    @foreach (data_get($column, 'links', []) as $link)
                        <p>
                            {{ is_array($link) ? data_get($link, 'label', data_get($link, 'title', '')) : $link }}
                        </p>
                    @endforeach
                </section>
            @endforeach
        </div>
    </div>
</footer>
