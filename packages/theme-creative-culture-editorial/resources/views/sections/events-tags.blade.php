@php
    $items = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-creative-culture-editorial::sections.events.planning_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.events.planning_summary'), 'meta' => __('capell-theme-creative-culture-editorial::sections.events.planning_meta')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.events.workshop_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.events.workshop_summary'), 'meta' => __('capell-theme-creative-culture-editorial::sections.events.workshop_meta')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.events.systems_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.events.systems_summary'), 'meta' => __('capell-theme-creative-culture-editorial::sections.events.systems_meta')],
    ]));
    $tags = data_get($section, 'tags', [
        __('capell-theme-creative-culture-editorial::sections.events.tag_typography'),
        __('capell-theme-creative-culture-editorial::sections.events.tag_portfolios'),
        __('capell-theme-creative-culture-editorial::sections.events.tag_criticism'),
        __('capell-theme-creative-culture-editorial::sections.events.tag_motion'),
        __('capell-theme-creative-culture-editorial::sections.events.tag_books'),
        __('capell-theme-creative-culture-editorial::sections.events.tag_posters'),
    ]);
@endphp

<section
    id="events-tags"
    class="cce-section"
>
    <div class="cce-section-inner">
        <p class="cce-kicker">
            {{ __('capell-theme-creative-culture-editorial::sections.events.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-creative-culture-editorial::sections.events.heading')) }}
        </h2>
        <p class="cce-lede">
            {{ data_get($section, 'summary', __('capell-theme-creative-culture-editorial::sections.events.summary')) }}
        </p>
        <div class="cce-grid">
            @foreach ($items as $item)
                <article class="cce-card">
                    <p class="cce-meta">
                        {{ data_get($item, 'meta', data_get($item, 'category', '')) }}
                    </p>
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
        <div class="cce-tag-row">
            @foreach ($tags as $tag)
                <span class="cce-tag">{{ $tag }}</span>
            @endforeach
        </div>
    </div>
</section>
