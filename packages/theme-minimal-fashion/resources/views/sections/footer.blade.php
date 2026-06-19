@php
    $columns = data_get($section, 'items', [
        ['title' => __('capell-theme-minimal-fashion::sections.footer.shop'), 'links' => [__('capell-theme-minimal-fashion::sections.footer.women'), __('capell-theme-minimal-fashion::sections.footer.men'), __('capell-theme-minimal-fashion::sections.footer.accessories')]],
        ['title' => __('capell-theme-minimal-fashion::sections.footer.service'), 'links' => [__('capell-theme-minimal-fashion::sections.footer.care'), __('capell-theme-minimal-fashion::sections.footer.alterations'), __('capell-theme-minimal-fashion::sections.footer.stores')]],
        ['title' => __('capell-theme-minimal-fashion::sections.footer.editorial'), 'links' => [__('capell-theme-minimal-fashion::sections.footer.lookbook'), __('capell-theme-minimal-fashion::sections.footer.materials'), __('capell-theme-minimal-fashion::sections.footer.newsletter')]],
    ]);
@endphp

<footer class="fashion-section fashion-section-dark">
    <div class="fashion-section-inner">
        <div class="fashion-grid">
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
