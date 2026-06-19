@php
    $columns = data_get($section, 'items', [
        ['title' => __('capell-theme-bold-sport-commerce::sections.footer.shop'), 'links' => [__('capell-theme-bold-sport-commerce::sections.footer.men'), __('capell-theme-bold-sport-commerce::sections.footer.women'), __('capell-theme-bold-sport-commerce::sections.footer.kids')]],
        ['title' => __('capell-theme-bold-sport-commerce::sections.footer.service'), 'links' => [__('capell-theme-bold-sport-commerce::sections.footer.teams'), __('capell-theme-bold-sport-commerce::sections.footer.shipping'), __('capell-theme-bold-sport-commerce::sections.footer.returns')]],
        ['title' => __('capell-theme-bold-sport-commerce::sections.footer.editorial'), 'links' => [__('capell-theme-bold-sport-commerce::sections.footer.training'), __('capell-theme-bold-sport-commerce::sections.footer.offers'), __('capell-theme-bold-sport-commerce::sections.footer.newsletter')]],
    ]);
@endphp

<footer class="sport-section sport-section-dark">
    <div class="sport-section-inner">
        <div class="sport-grid">
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
