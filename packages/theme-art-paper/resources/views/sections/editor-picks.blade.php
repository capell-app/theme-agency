@php
    $heading = data_get($section, 'heading', __('capell-theme-art-paper::sections.picks.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-art-paper::sections.picks.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-art-paper::sections.picks.home_title'), 'summary' => __('capell-theme-art-paper::sections.picks.home_summary'), 'meta' => __('capell-theme-art-paper::sections.picks.home_meta')],
        ['title' => __('capell-theme-art-paper::sections.picks.chair_title'), 'summary' => __('capell-theme-art-paper::sections.picks.chair_summary'), 'meta' => __('capell-theme-art-paper::sections.picks.chair_meta')],
        ['title' => __('capell-theme-art-paper::sections.picks.gallery_title'), 'summary' => __('capell-theme-art-paper::sections.picks.gallery_summary'), 'meta' => __('capell-theme-art-paper::sections.picks.gallery_meta')],
    ]);
    $plateShapes = ['dlm-plate-arch', 'dlm-plate-disc', 'dlm-plate-column'];
@endphp

<section
    id="editor-picks"
    class="dlm-section"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-art-paper::sections.picks.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="dlm-lede">{{ $summary }}</p>

        <div class="dlm-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $plateShape = $plateShapes[($loop->index) % count($plateShapes)];
                @endphp

                <article class="dlm-card">
                    <figure class="dlm-plate">
                        <div class="dlm-plate-frame">
                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    class="dlm-plate-media"
                                />
                            @else
                                <div
                                    class="dlm-plate-media dlm-plate-media-empty {{ $plateShape }}"
                                    aria-hidden="true"
                                ></div>
                            @endif
                        </div>
                        <figcaption class="dlm-plate-caption">
                            <span class="dlm-plate-number">
                                {{ __('capell-theme-art-paper::sections.plate.pick') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span>
                                {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-art-paper::sections.picks.default_meta'))) }}
                            </span>
                        </figcaption>
                    </figure>
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="dlm-title-link"
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
                    <p class="dlm-meta">
                        {{ data_get($item, 'care_note', __('capell-theme-art-paper::sections.picks.care_note')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
