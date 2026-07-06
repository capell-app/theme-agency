{{--
    latest-designs-showcase (Wave 4c signature widget #2): a wider showcase
    variant of the default latest-designs grid — the lead capture spans two
    tessellation columns so the newest entry reads as a featured showcase
    rather than one more grid tile, while the remaining captures keep the
    same dense auto-fit rhythm.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.latest.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-field-guide::sections.latest.summary'));
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
    $buttonUrl = data_get($section, 'url', '#taxonomy-grid-browser');
    $buttonLabel = data_get($section, 'label', __('capell-theme-field-guide::sections.latest.button'));
@endphp

<section
    id="latest-designs"
    class="fga-section"
    data-widget="latest-designs-showcase"
    data-variant="showcase-wide"
>
    <div class="fga-section-inner">
        <div class="fga-section-head">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-field-guide::sections.latest.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="fga-lede">{{ $summary }}</p>
            </div>
            <p class="fga-mono-note">
                {{ __('capell-theme-field-guide::sections.latest.count_note') }}
            </p>
        </div>

        <div class="fga-dense-grid fga-showcase-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTags = data_get($item, 'tags', []);
                    $isShowcasedLead = $loop->first;
                @endphp

                <article
                    class="fga-capture {{ $isShowcasedLead ? 'fga-showcase-lead' : '' }}"
                >
                    @if (filled($itemImage))
                        <img
                            src="{{ $itemImage }}"
                            alt="{{ $itemAlt }}"
                            loading="lazy"
                            decoding="async"
                            class="fga-capture-media {{ $isShowcasedLead ? 'fga-capture-media-wide' : '' }}"
                        />
                    @else
                        <div
                            class="fga-capture-media fga-capture-media-empty {{ $isShowcasedLead ? 'fga-capture-media-wide' : '' }}"
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
                            @if ($isShowcasedLead)
                                <span class="fga-chip fga-chip-type">
                                    {{ __('capell-theme-field-guide::sections.latest.showcase_label') }}
                                </span>
                            @else
                                <span class="fga-capture-index">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            @endif
                        </div>
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

                        @if (filled(data_get($item, 'meta', data_get($item, 'category'))))
                            <p class="fga-capture-source">
                                {{ __('capell-theme-field-guide::sections.latest.added_label') }} {{ data_get($item, 'meta', data_get($item, 'category', '')) }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="fga-actions">
            <a
                class="fga-button fga-button-secondary"
                href="{{ $buttonUrl }}"
            >
                {{ $buttonLabel }}
            </a>
        </div>
    </div>
</section>
