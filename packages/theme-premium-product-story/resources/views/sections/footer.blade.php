@php
    $columns = data_get($section, 'items', [
        ['title' => __('capell-theme-premium-product-story::sections.footer.shop'), 'links' => [__('capell-theme-premium-product-story::sections.footer.models'), __('capell-theme-premium-product-story::sections.footer.accessories'), __('capell-theme-premium-product-story::sections.footer.compare')]],
        ['title' => __('capell-theme-premium-product-story::sections.footer.service'), 'links' => [__('capell-theme-premium-product-story::sections.footer.support'), __('capell-theme-premium-product-story::sections.footer.trade_in'), __('capell-theme-premium-product-story::sections.footer.financing')]],
        ['title' => __('capell-theme-premium-product-story::sections.footer.editorial'), 'links' => [__('capell-theme-premium-product-story::sections.footer.stories'), __('capell-theme-premium-product-story::sections.footer.updates'), __('capell-theme-premium-product-story::sections.footer.newsletter')]],
    ]);
@endphp

<footer class="product-section product-section-dark">
    <div class="product-section-inner">
        <div class="product-grid">
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
