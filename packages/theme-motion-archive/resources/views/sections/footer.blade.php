@php
    $columns = data_get($section, 'items', [
        ['title' => __('capell-theme-motion-archive::sections.footer.shop'), 'links' => [__('capell-theme-motion-archive::sections.footer.models'), __('capell-theme-motion-archive::sections.footer.accessories'), __('capell-theme-motion-archive::sections.footer.compare')]],
        ['title' => __('capell-theme-motion-archive::sections.footer.service'), 'links' => [__('capell-theme-motion-archive::sections.footer.support'), __('capell-theme-motion-archive::sections.footer.trade_in'), __('capell-theme-motion-archive::sections.footer.financing')]],
        ['title' => __('capell-theme-motion-archive::sections.footer.editorial'), 'links' => [__('capell-theme-motion-archive::sections.footer.stories'), __('capell-theme-motion-archive::sections.footer.updates'), __('capell-theme-motion-archive::sections.footer.newsletter')]],
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
