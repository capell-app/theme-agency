@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.picks.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-field-guide::sections.picks.summary'));
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
    $mediaShapes = ['fga-capture-media-tall', 'fga-capture-media', 'fga-capture-media-wide'];
@endphp

<section
    id="editor-picks"
    class="fga-section fga-section-panel"
>
    <div class="fga-section-inner">
        <div class="fga-section-head">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-field-guide::sections.picks.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="fga-lede">{{ $summary }}</p>
            </div>
            <p class="fga-mono-note">
                {{ __('capell-theme-field-guide::sections.picks.count_note') }}
            </p>
        </div>

        <div class="fga-masonry">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTags = data_get($item, 'tags', []);
                    $mediaShape = $mediaShapes[$loop->index % count($mediaShapes)];
                @endphp

                <article class="fga-capture">
                    @if (filled($itemImage))
                        <img
                            src="{{ $itemImage }}"
                            alt="{{ $itemAlt }}"
                            loading="lazy"
                            decoding="async"
                            class="fga-capture-media {{ $mediaShape }}"
                        />
                    @else
                        <div
                            class="fga-capture-media {{ $mediaShape }} fga-capture-media-empty"
                            aria-hidden="true"
                        ></div>
                    @endif
                    <div class="fga-capture-body">
                        <div class="fga-capture-title-row">
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="fga-title-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                @endif
                            </h3>
                            <span class="fga-capture-index">
                                {{ __('capell-theme-field-guide::sections.picks.pick_label') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        @if (filled(data_get($item, 'summary', data_get($item, 'description'))))
                            <p>
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        @endif

                        @if (is_iterable($itemTags) && collect($itemTags)->isNotEmpty())
                            <ul class="fga-chip-row">
                                @foreach ($itemTags as $tag)
                                    <li>
                                        <span
                                            class="fga-chip fga-chip-{{ data_get($tag, 'facet', 'type') }}"
                                        >
                                            {{ data_get($tag, 'label', is_string($tag) ? $tag : '') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <p class="fga-capture-source">
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-field-guide::sections.picks.default_source'))) }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
