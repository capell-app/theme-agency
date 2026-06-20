@php
    $columns = data_get($section, 'items', [
        ['title' => __('capell-theme-raw-index::sections.footer.shop'), 'links' => [__('capell-theme-raw-index::sections.footer.models'), __('capell-theme-raw-index::sections.footer.accessories'), __('capell-theme-raw-index::sections.footer.compare')]],
        ['title' => __('capell-theme-raw-index::sections.footer.service'), 'links' => [__('capell-theme-raw-index::sections.footer.support'), __('capell-theme-raw-index::sections.footer.trade_in'), __('capell-theme-raw-index::sections.footer.financing')]],
        ['title' => __('capell-theme-raw-index::sections.footer.editorial'), 'links' => [__('capell-theme-raw-index::sections.footer.stories'), __('capell-theme-raw-index::sections.footer.updates'), __('capell-theme-raw-index::sections.footer.newsletter')]],
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
