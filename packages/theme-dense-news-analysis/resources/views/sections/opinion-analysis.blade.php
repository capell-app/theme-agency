@php
    $heading = data_get($section, 'heading', __('capell-theme-dense-news-analysis::sections.opinion.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-dense-news-analysis::sections.opinion.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-dense-news-analysis::sections.opinion.home_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.opinion.home_summary'), 'meta' => __('capell-theme-dense-news-analysis::sections.opinion.home_meta')],
        ['title' => __('capell-theme-dense-news-analysis::sections.opinion.chair_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.opinion.chair_summary'), 'meta' => __('capell-theme-dense-news-analysis::sections.opinion.chair_meta')],
        ['title' => __('capell-theme-dense-news-analysis::sections.opinion.gallery_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.opinion.gallery_summary'), 'meta' => __('capell-theme-dense-news-analysis::sections.opinion.gallery_meta')],
    ]);
@endphp

<section class="editorial-section">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-dense-news-analysis::sections.opinion.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="editorial-lede">{{ $summary }}</p>

        <div class="editorial-grid">
            @foreach ($items as $item)
                <article class="editorial-card editorial-showcase-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <div class="editorial-showcase-specs">
                        <span>
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-dense-news-analysis::sections.opinion.default_meta'))) }}
                        </span>
                        <span>
                            {{ data_get($item, 'care_note', __('capell-theme-dense-news-analysis::sections.opinion.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
