@php
    $heading = data_get($section, 'heading', __('capell-theme-experimental-directory::sections.featured.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-experimental-directory::sections.featured.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-experimental-directory::sections.featured.launch_title'), 'summary' => __('capell-theme-experimental-directory::sections.featured.launch_summary'), 'meta' => __('capell-theme-experimental-directory::sections.featured.launch_meta')],
    ]);
    $items = collect($items)->values();
    $lead = $items->first();
    $rest = $items->slice(1);
@endphp

<section
    id="featured-today"
    class="exd-section"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-experimental-directory::sections.featured.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        @if ($lead)
            @php
                $leadImage = data_get($lead, 'image', data_get($lead, 'imageUrl'));
                $leadAlt = data_get($lead, 'imageAlt', data_get($lead, 'title', ''));
                $leadUrl = data_get($lead, 'url', data_get($lead, 'href'));
                $leadTitle = data_get($lead, 'title', data_get($lead, 'name', ''));
            @endphp

            <div class="exd-feature-slab">
                <div class="exd-feature-frame">
                    <span class="exd-feature-tag">
                        {{ __('capell-theme-experimental-directory::sections.featured.tag') }}
                    </span>

                    @if (filled($leadImage))
                        <img
                            src="{{ $leadImage }}"
                            alt="{{ $leadAlt }}"
                            width="1000"
                            height="800"
                            loading="lazy"
                            decoding="async"
                            class="exd-media"
                        />
                    @else
                        <div
                            class="exd-media exd-media-empty"
                            aria-hidden="true"
                        ></div>
                    @endif
                </div>
                <div>
                    <p class="exd-feature-meta">
                        {{ data_get($lead, 'meta', '') }}
                    </p>
                    <h3 class="exd-feature-title">
                        @if (filled($leadUrl))
                            <a
                                class="exd-title-link"
                                href="{{ $leadUrl }}"
                            >
                                {{ $leadTitle }}
                            </a>
                        @else
                            {{ $leadTitle }}
                        @endif
                    </h3>
                    <p>{{ data_get($lead, 'summary', '') }}</p>
                </div>
            </div>
        @endif

        @if ($rest->isNotEmpty())
            <div class="exd-grid">
                @foreach ($rest as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    @endphp

                    @if (filled($itemUrl))
                        <a
                            class="exd-card"
                            href="{{ $itemUrl }}"
                        >
                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    width="800"
                                    height="600"
                                    loading="lazy"
                                    decoding="async"
                                    class="exd-media"
                                />
                            @else
                                <div
                                    class="exd-media exd-media-empty"
                                    aria-hidden="true"
                                ></div>
                            @endif
                            <div class="exd-card-body">
                                <p class="exd-meta">
                                    {{ data_get($item, 'meta', '') }}
                                </p>
                                <h3>
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                </h3>
                                <p>{{ data_get($item, 'summary', '') }}</p>
                            </div>
                        </a>
                    @else
                        <article class="exd-card">
                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    width="800"
                                    height="600"
                                    loading="lazy"
                                    decoding="async"
                                    class="exd-media"
                                />
                            @else
                                <div
                                    class="exd-media exd-media-empty"
                                    aria-hidden="true"
                                ></div>
                            @endif
                            <div class="exd-card-body">
                                <p class="exd-meta">
                                    {{ data_get($item, 'meta', '') }}
                                </p>
                                <h3>
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                </h3>
                                <p>{{ data_get($item, 'summary', '') }}</p>
                            </div>
                        </article>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</section>
