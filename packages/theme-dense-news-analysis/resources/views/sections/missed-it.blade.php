@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-dense-news-analysis::sections.recap.palette_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.recap.palette_summary')],
        ['title' => __('capell-theme-dense-news-analysis::sections.recap.rooms_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.recap.rooms_summary')],
        ['title' => __('capell-theme-dense-news-analysis::sections.recap.objects_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.recap.objects_summary')],
    ]);
@endphp

<section
    class="editorial-section"
    style="background: var(--editorial-field)"
>
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-dense-news-analysis::sections.recap.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-dense-news-analysis::sections.recap.heading')) }}
        </h2>
        <p class="editorial-lede">
            {{ data_get($section, 'summary', __('capell-theme-dense-news-analysis::sections.recap.summary')) }}
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
