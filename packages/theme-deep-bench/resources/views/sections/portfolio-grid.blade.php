@php
    $heading = data_get($section, 'heading', __('capell-theme-deep-bench::sections.grid.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-deep-bench::sections.grid.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-deep-bench::sections.grid.wells_title'), 'meta' => __('capell-theme-deep-bench::sections.grid.wells_meta'), 'summary' => __('capell-theme-deep-bench::sections.grid.wells_summary')],
        ['title' => __('capell-theme-deep-bench::sections.grid.sol_title'), 'meta' => __('capell-theme-deep-bench::sections.grid.sol_meta'), 'summary' => __('capell-theme-deep-bench::sections.grid.sol_summary')],
        ['title' => __('capell-theme-deep-bench::sections.grid.devon_title'), 'meta' => __('capell-theme-deep-bench::sections.grid.devon_meta'), 'summary' => __('capell-theme-deep-bench::sections.grid.devon_summary')],
    ]);
@endphp

<section
    id="portfolio-grid"
    class="pfd-section"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.grid.eyebrow')) }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="pfd-lede">{{ $summary }}</p>

        <div class="pfd-cards">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTags = data_get($item, 'tags', []);
                    $itemAvailable = (bool) data_get($item, 'available', false);
                @endphp

                <article class="pfd-card">
                    <div class="pfd-card-head">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="pfd-photo pfd-avatar"
                            />
                        @else
                            <div
                                class="pfd-photo pfd-photo-empty pfd-avatar"
                                aria-hidden="true"
                            ></div>
                        @endif
                        <div>
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
                            <p class="pfd-role">
                                {{ data_get($item, 'meta', data_get($item, 'category', '')) }}
                            </p>
                        </div>
                    </div>

                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>

                    @if (is_iterable($itemTags) && collect($itemTags)->isNotEmpty())
                        <ul class="pfd-tags">
                            @foreach ($itemTags as $tag)
                                <li>
                                    {{ is_array($tag) ? data_get($tag, 'label', '') : $tag }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <p class="pfd-meta">
                        @if (filled(data_get($item, 'location')))
                            <span>{{ data_get($item, 'location') }}</span>
                        @endif

                        @if ($itemAvailable)
                            <span>
                                <span
                                    class="pfd-dot"
                                    aria-hidden="true"
                                ></span>
                                {{ __('capell-theme-deep-bench::sections.grid.available') }}
                            </span>
                        @endif
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
