@php
    $heading = data_get($section, 'heading', __('capell-theme-portfolio-directory::sections.lists.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-portfolio-directory::sections.lists.summary'));
    $collections = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-portfolio-directory::sections.lists.motion_title'), 'summary' => __('capell-theme-portfolio-directory::sections.lists.motion_summary'), 'meta' => __('capell-theme-portfolio-directory::sections.lists.motion_meta')],
        ['title' => __('capell-theme-portfolio-directory::sections.lists.frontend_title'), 'summary' => __('capell-theme-portfolio-directory::sections.lists.frontend_summary'), 'meta' => __('capell-theme-portfolio-directory::sections.lists.frontend_meta')],
        ['title' => __('capell-theme-portfolio-directory::sections.lists.studios_title'), 'summary' => __('capell-theme-portfolio-directory::sections.lists.studios_summary'), 'meta' => __('capell-theme-portfolio-directory::sections.lists.studios_meta')],
    ]));
@endphp

<section
    id="curated-lists"
    class="pfd-section"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-portfolio-directory::sections.lists.eyebrow')) }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="pfd-lede">{{ $summary }}</p>

        <div class="pfd-collections">
            @foreach ($collections as $collection)
                @php
                    $collectionUrl = data_get($collection, 'url', data_get($collection, 'href'));
                @endphp

                <article class="pfd-collection">
                    <h3>
                        @if (filled($collectionUrl))
                            <a
                                class="pfd-title-link"
                                href="{{ $collectionUrl }}"
                            >
                                {{ data_get($collection, 'title', data_get($collection, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($collection, 'title', data_get($collection, 'name', '')) }}
                        @endif
                    </h3>
                    <p>
                        {{ data_get($collection, 'summary', data_get($collection, 'description', '')) }}
                    </p>
                    <span class="pfd-rail-meta">
                        {{ data_get($collection, 'meta', data_get($collection, 'category', '')) }}
                    </span>
                    <span
                        class="pfd-collection-arrow"
                        aria-hidden="true"
                    >
                        &rarr;
                    </span>
                </article>
            @endforeach
        </div>
    </div>
</section>
