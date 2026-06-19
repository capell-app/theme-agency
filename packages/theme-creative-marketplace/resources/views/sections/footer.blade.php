@php
    $columns = data_get($section, 'items', [
        ['title' => __('capell-theme-creative-marketplace::sections.footer.shop'), 'links' => [__('capell-theme-creative-marketplace::sections.footer.models'), __('capell-theme-creative-marketplace::sections.footer.accessories'), __('capell-theme-creative-marketplace::sections.footer.compare')]],
        ['title' => __('capell-theme-creative-marketplace::sections.footer.service'), 'links' => [__('capell-theme-creative-marketplace::sections.footer.support'), __('capell-theme-creative-marketplace::sections.footer.trade_in'), __('capell-theme-creative-marketplace::sections.footer.financing')]],
        ['title' => __('capell-theme-creative-marketplace::sections.footer.editorial'), 'links' => [__('capell-theme-creative-marketplace::sections.footer.stories'), __('capell-theme-creative-marketplace::sections.footer.updates'), __('capell-theme-creative-marketplace::sections.footer.newsletter')]],
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
