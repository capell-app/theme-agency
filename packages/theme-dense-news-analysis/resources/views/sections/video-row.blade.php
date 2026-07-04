@php
    $stories = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-dense-news-analysis::sections.video.lighting_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.video.lighting_summary'), 'meta' => __('capell-theme-dense-news-analysis::sections.video.lighting_meta')],
        ['title' => __('capell-theme-dense-news-analysis::sections.video.materials_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.video.materials_summary'), 'meta' => __('capell-theme-dense-news-analysis::sections.video.materials_meta')],
        ['title' => __('capell-theme-dense-news-analysis::sections.video.books_title'), 'summary' => __('capell-theme-dense-news-analysis::sections.video.books_summary'), 'meta' => __('capell-theme-dense-news-analysis::sections.video.books_meta')],
    ]));
@endphp

<section
    id="video-row"
    class="dnews-section"
>
    <div class="dnews-section-inner">
        <div class="dnews-section-head">
            <p class="dnews-kicker">
                {{ __('capell-theme-dense-news-analysis::sections.video.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-dense-news-analysis::sections.video.heading')) }}
            </h2>
            @if (data_get($section, 'summary') !== null)
                <p class="dnews-lede">
                    {{ data_get($section, 'summary') }}
                </p>
            @endif
        </div>

        <div class="dnews-grid">
            @foreach ($stories as $story)
                @php
                    $storyUrl = data_get($story, 'url', data_get($story, 'href'));
                @endphp

                <article class="dnews-card">
                    <div
                        class="dnews-tile dnews-tile-dark"
                        aria-hidden="true"
                    >
                        <span class="dnews-tile-play"></span>
                        <span class="dnews-tile-label">
                            {{ data_get($story, 'meta', data_get($story, 'category', __('capell-theme-dense-news-analysis::sections.video.kicker'))) }}
                        </span>
                    </div>
                    <h3>
                        @if ($storyUrl !== null)
                            <a
                                class="dnews-title-link"
                                href="{{ $storyUrl }}"
                            >
                                {{ data_get($story, 'title', data_get($story, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($story, 'title', data_get($story, 'name', '')) }}
                        @endif
                    </h3>
                    <p>
                        {{ data_get($story, 'summary', data_get($story, 'description', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
