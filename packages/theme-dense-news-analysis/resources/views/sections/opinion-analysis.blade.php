@php
    $heading = data_get($section, 'heading', __('capell-theme-dense-news-analysis::sections.opinion.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-dense-news-analysis::sections.opinion.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-dense-news-analysis::sections.opinion.home_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.opinion.home_summary'), 'meta' => __('capell-theme-dense-news-analysis::sections.opinion.home_meta')],
        ['title' => __('capell-theme-dense-news-analysis::sections.opinion.chair_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.opinion.chair_summary'), 'meta' => __('capell-theme-dense-news-analysis::sections.opinion.chair_meta')],
        ['title' => __('capell-theme-dense-news-analysis::sections.opinion.gallery_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.opinion.gallery_summary'), 'meta' => __('capell-theme-dense-news-analysis::sections.opinion.gallery_meta')],
    ]);
@endphp

<section
    id="opinion-analysis"
    class="dnews-section"
>
    <div class="dnews-section-inner">
        <div class="dnews-section-head">
            <p class="dnews-kicker">
                {{ __('capell-theme-dense-news-analysis::sections.opinion.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="dnews-lede">{{ $summary }}</p>
        </div>

        <div class="dnews-grid">
            @foreach ($items as $item)
                @php
                    $label = data_get($item, 'meta', data_get($item, 'category', __('capell-theme-dense-news-analysis::sections.opinion.default_meta')));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="dnews-card">
                    <div
                        class="dnews-tile"
                        aria-hidden="true"
                    >
                        <span class="dnews-tile-index">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="dnews-tile-label">{{ $label }}</span>
                    </div>
                    <h3>
                        @if ($itemUrl !== null)
                            <a
                                class="dnews-title-link"
                                href="{{ $itemUrl }}"
                            >
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        @endif
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <p class="dnews-meta">
                        {{ data_get($item, 'care_note', __('capell-theme-dense-news-analysis::sections.opinion.care_note')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
