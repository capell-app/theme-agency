@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-dense-news-analysis::sections.topics.architecture_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.topics.architecture_summary')],
        ['title' => __('capell-theme-dense-news-analysis::sections.topics.interiors_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.topics.interiors_summary')],
        ['title' => __('capell-theme-dense-news-analysis::sections.topics.fashion_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.topics.fashion_summary')],
        ['title' => __('capell-theme-dense-news-analysis::sections.topics.art_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.topics.art_summary')],
    ]);
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-dense-news-analysis::sections.topics.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-dense-news-analysis::sections.topics.heading')) }}
        </h2>
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
