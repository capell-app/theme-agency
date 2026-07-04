@php
    $heading = data_get($section, 'heading', __('capell-theme-creative-culture-editorial::sections.featured.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-creative-culture-editorial::sections.featured.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-creative-culture-editorial::sections.featured.launch_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.featured.launch_summary'), 'meta' => __('capell-theme-creative-culture-editorial::sections.featured.launch_meta')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.featured.essay_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.featured.essay_summary'), 'meta' => __('capell-theme-creative-culture-editorial::sections.featured.essay_meta')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.featured.template_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.featured.template_summary'), 'meta' => __('capell-theme-creative-culture-editorial::sections.featured.template_meta')],
    ]);
@endphp

<section
    id="must-reads"
    class="cce-section"
>
    <div class="cce-section-inner">
        <p class="cce-kicker">
            {{ __('capell-theme-creative-culture-editorial::sections.featured.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="cce-lede">{{ $summary }}</p>

        <div class="cce-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemInitial = mb_substr(trim((string) data_get($item, 'title', '')), 0, 1);
                @endphp

                @if (filled($itemUrl))
                    <a
                        class="cce-card cce-card-link"
                        href="{{ $itemUrl }}"
                    >
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="cce-media cce-media-lead"
                            />
                        @else
                            <div
                                class="cce-media cce-media-lead cce-media-empty"
                                data-initial="{{ $itemInitial }}"
                                aria-hidden="true"
                            ></div>
                        @endif
                        <p class="cce-meta">
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-creative-culture-editorial::sections.featured.default_meta'))) }}
                        </p>
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </a>
                @else
                    <article class="cce-card">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="cce-media cce-media-lead"
                            />
                        @else
                            <div
                                class="cce-media cce-media-lead cce-media-empty"
                                data-initial="{{ $itemInitial }}"
                                aria-hidden="true"
                            ></div>
                        @endif
                        <p class="cce-meta">
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-creative-culture-editorial::sections.featured.default_meta'))) }}
                        </p>
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>
