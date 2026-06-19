@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-global-culture-magazine::sections.columnists.palette_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.columnists.palette_summary')],
        ['title' => __('capell-theme-global-culture-magazine::sections.columnists.rooms_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.columnists.rooms_summary')],
        ['title' => __('capell-theme-global-culture-magazine::sections.columnists.objects_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.columnists.objects_summary')],
    ]);
@endphp

<section
    class="editorial-section"
    style="background: var(--editorial-field)"
>
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-global-culture-magazine::sections.columnists.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-global-culture-magazine::sections.columnists.heading')) }}
        </h2>
        <p class="editorial-lede">
            {{ data_get($section, 'summary', __('capell-theme-global-culture-magazine::sections.columnists.summary')) }}
        </p>
        <div class="editorial-grid">
            @foreach ($items as $item)
                <article class="editorial-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
