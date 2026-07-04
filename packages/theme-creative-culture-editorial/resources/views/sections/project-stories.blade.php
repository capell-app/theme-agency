@php
    $heading = data_get($section, 'heading', __('capell-theme-creative-culture-editorial::sections.stories.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-creative-culture-editorial::sections.stories.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-creative-culture-editorial::sections.stories.product_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.stories.product_summary'), 'meta' => __('capell-theme-creative-culture-editorial::sections.stories.product_meta')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.stories.design_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.stories.design_summary'), 'meta' => __('capell-theme-creative-culture-editorial::sections.stories.design_meta')],
        ['title' => __('capell-theme-creative-culture-editorial::sections.stories.advice_title'), 'summary' => __('capell-theme-creative-culture-editorial::sections.stories.advice_summary'), 'meta' => __('capell-theme-creative-culture-editorial::sections.stories.advice_meta')],
    ]);
@endphp

<section
    id="project-stories"
    class="cce-section"
>
    <div class="cce-section-inner">
        <p class="cce-kicker">
            {{ __('capell-theme-creative-culture-editorial::sections.stories.kicker') }}
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

                <article class="cce-card">
                    @if (filled($itemImage))
                        <img
                            src="{{ $itemImage }}"
                            alt="{{ $itemAlt }}"
                            loading="lazy"
                            decoding="async"
                            class="cce-media"
                        />
                    @else
                        <div
                            class="cce-media cce-media-empty"
                            data-initial="{{ $itemInitial }}"
                            aria-hidden="true"
                        ></div>
                    @endif
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="cce-title-link"
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
                    <p class="cce-meta">
                        {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-creative-culture-editorial::sections.stories.default_meta'))) }}
                        &middot;
                        {{ data_get($item, 'care_note', __('capell-theme-creative-culture-editorial::sections.stories.care_note')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
