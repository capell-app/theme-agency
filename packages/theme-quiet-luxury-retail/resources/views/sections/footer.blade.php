@php
    $columns = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.footer.shop'), 'links' => [__('capell-theme-quiet-luxury-retail::sections.footer.women'), __('capell-theme-quiet-luxury-retail::sections.footer.men'), __('capell-theme-quiet-luxury-retail::sections.footer.accessories')]],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.footer.service'), 'links' => [__('capell-theme-quiet-luxury-retail::sections.footer.care'), __('capell-theme-quiet-luxury-retail::sections.footer.alterations'), __('capell-theme-quiet-luxury-retail::sections.footer.stores')]],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.footer.editorial'), 'links' => [__('capell-theme-quiet-luxury-retail::sections.footer.lookbook'), __('capell-theme-quiet-luxury-retail::sections.footer.materials'), __('capell-theme-quiet-luxury-retail::sections.footer.newsletter')]],
    ]);
@endphp

<footer class="luxury-section luxury-section-dark">
    <div class="luxury-section-inner">
        <div class="luxury-grid">
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
