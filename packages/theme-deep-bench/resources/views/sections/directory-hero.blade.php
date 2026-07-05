@php
    $heading = data_get($section, 'heading', __('capell-theme-deep-bench::sections.featured.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-deep-bench::sections.featured.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-deep-bench::sections.featured.marlow_title'), 'summary' => __('capell-theme-deep-bench::sections.featured.marlow_summary')],
        ['title' => __('capell-theme-deep-bench::sections.featured.tideline_title'), 'summary' => __('capell-theme-deep-bench::sections.featured.tideline_summary')],
        ['title' => __('capell-theme-deep-bench::sections.featured.northglass_title'), 'summary' => __('capell-theme-deep-bench::sections.featured.northglass_summary')],
    ]);
@endphp

<section
    id="directory-hero"
    class="pfd-section pfd-section-field"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.featured.eyebrow')) }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="pfd-lede">{{ $summary }}</p>

        <div class="pfd-featured">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="pfd-card">
                    @if (filled($itemImage))
                        <img
                            src="{{ $itemImage }}"
                            alt="{{ $itemAlt }}"
                            loading="lazy"
                            decoding="async"
                            class="pfd-photo pfd-cover-wide"
                        />
                    @else
                        <div
                            class="pfd-photo pfd-photo-empty pfd-cover-wide"
                            aria-hidden="true"
                        ></div>
                    @endif
                    <span class="pfd-featured-rank">
                        {{ __('capell-theme-deep-bench::sections.featured.rank') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="pfd-title-link"
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
                </article>
            @endforeach
        </div>
    </div>
</section>
