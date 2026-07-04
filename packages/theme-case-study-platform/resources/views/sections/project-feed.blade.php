@php
    $heading = data_get($section, 'heading', __('capell-theme-case-study-platform::sections.project_feed.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-case-study-platform::sections.project_feed.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="project-feed"
    class="csp-section"
>
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-case-study-platform::sections.project_feed.heading') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="csp-lede">{{ $summary }}</p>

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="csp-project-grid">
                @foreach ($items as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $title = data_get($item, 'title', data_get($item, 'name', ''));
                    @endphp

                    <article>
                        @if (filled($itemUrl))
                            <a
                                class="csp-project-card"
                                href="{{ $itemUrl }}"
                            >
                                @if (filled($itemImage))
                                    <img
                                        src="{{ $itemImage }}"
                                        alt="{{ $itemAlt }}"
                                        loading="lazy"
                                        decoding="async"
                                        class="csp-cover"
                                    />
                                @else
                                    <div
                                        class="csp-cover csp-cover-empty"
                                        aria-hidden="true"
                                    ></div>
                                @endif
                                <p class="csp-project-meta">
                                    {{ data_get($item, 'meta', __('capell-theme-case-study-platform::sections.project_feed.default_meta')) }}
                                </p>
                                <h3>{{ $title }}</h3>
                                <p>
                                    {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                                </p>
                                <span class="csp-project-stat">
                                    {{ data_get($item, 'care_note', __('capell-theme-case-study-platform::sections.project_feed.default_stat')) }}
                                </span>
                            </a>
                        @else
                            <div class="csp-project-card">
                                @if (filled($itemImage))
                                    <img
                                        src="{{ $itemImage }}"
                                        alt="{{ $itemAlt }}"
                                        loading="lazy"
                                        decoding="async"
                                        class="csp-cover"
                                    />
                                @else
                                    <div
                                        class="csp-cover csp-cover-empty"
                                        aria-hidden="true"
                                    ></div>
                                @endif
                                <p class="csp-project-meta">
                                    {{ data_get($item, 'meta', __('capell-theme-case-study-platform::sections.project_feed.default_meta')) }}
                                </p>
                                <h3>{{ $title }}</h3>
                                <p>
                                    {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                                </p>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <p class="csp-lede">
                {{ __('capell-theme-case-study-platform::sections.project_feed.empty') }}
            </p>
        @endif
    </div>
</section>
