@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.listing.heading'));
    $summary = data_get($section, 'summary');
    $items = collect(is_iterable(data_get($section, 'items', data_get($section, 'posts', []))) ? data_get($section, 'items', data_get($section, 'posts', [])) : [])
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
@endphp

<section
    id="content-listing"
    class="fga-section"
>
    <div class="fga-section-inner">
        <div class="fga-section-head">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-field-guide::sections.listing.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                @if (filled($summary))
                    <p class="fga-lede">{{ $summary }}</p>
                @endif
            </div>
            <p class="fga-mono-note">
                {{ __('capell-theme-field-guide::sections.listing.count_note') }}
            </p>
        </div>

        @if ($items->isNotEmpty())
            <div class="fga-dense-grid">
                @foreach ($items as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemTags = data_get($item, 'tags', []);
                    @endphp

                    <article class="fga-capture">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="fga-capture-media"
                            />
                        @else
                            <div
                                class="fga-capture-media fga-capture-media-empty"
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
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
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

                            @if (filled(data_get($item, 'category', data_get($item, 'meta'))))
                                <p class="fga-capture-source">
                                    {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="fga-empty-state">
                <p class="fga-empty-state-kicker">
                    {{ __('capell-theme-field-guide::sections.listing.kicker') }}
                </p>
                <h3 class="fga-empty-state-heading">
                    {{ __('capell-theme-field-guide::sections.listing.empty_heading') }}
                </h3>
                <p class="fga-empty-state-body">
                    {{ __('capell-theme-field-guide::sections.listing.empty_body') }}
                </p>
                <a
                    class="fga-empty-state-reset"
                    href="#taxonomy-navigation"
                >
                    {{ __('capell-theme-field-guide::sections.listing.empty_reset_label') }}
                </a>
            </div>
        @endif
    </div>
</section>
