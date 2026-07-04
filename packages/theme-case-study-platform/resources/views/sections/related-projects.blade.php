@php
    $heading = data_get($section, 'heading', __('capell-theme-case-study-platform::sections.related_projects.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-case-study-platform::sections.related_projects.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="related-projects"
    class="csp-section csp-section-field"
>
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-case-study-platform::sections.related_projects.heading') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="csp-lede">{{ $summary }}</p>

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="csp-related-grid">
                @foreach ($items as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $title = data_get($item, 'title', data_get($item, 'name', ''));
                    @endphp

                    @if (filled($itemUrl))
                        <a
                            class="csp-related-card"
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
                                {{ data_get($item, 'meta', __('capell-theme-case-study-platform::sections.related_projects.default_meta')) }}
                            </p>
                            <h3 style="font-size: 1.05rem; margin: 0">
                                {{ $title }}
                            </h3>
                            <p style="margin: 0; color: var(--csp-muted)">
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        </a>
                    @else
                        <div class="csp-related-card">
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
                                {{ data_get($item, 'meta', __('capell-theme-case-study-platform::sections.related_projects.default_meta')) }}
                            </p>
                            <h3 style="font-size: 1.05rem; margin: 0">
                                {{ $title }}
                            </h3>
                            <p style="margin: 0; color: var(--csp-muted)">
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</section>
