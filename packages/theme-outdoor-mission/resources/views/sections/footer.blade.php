@php
    $columns = data_get($section, 'items', [
        ['title' => __('capell-theme-outdoor-mission::sections.footer.shop'), 'links' => [__('capell-theme-outdoor-mission::sections.footer.jackets'), __('capell-theme-outdoor-mission::sections.footer.packs'), __('capell-theme-outdoor-mission::sections.footer.layers')]],
        ['title' => __('capell-theme-outdoor-mission::sections.footer.care'), 'links' => [__('capell-theme-outdoor-mission::sections.footer.repairs'), __('capell-theme-outdoor-mission::sections.footer.reuse'), __('capell-theme-outdoor-mission::sections.footer.materials')]],
        ['title' => __('capell-theme-outdoor-mission::sections.footer.action'), 'links' => [__('capell-theme-outdoor-mission::sections.footer.campaigns'), __('capell-theme-outdoor-mission::sections.footer.petitions'), __('capell-theme-outdoor-mission::sections.footer.stories')]],
    ]);
@endphp

<footer class="outdoor-section outdoor-section-dark">
    <div class="outdoor-section-inner">
        <div class="outdoor-grid">
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
