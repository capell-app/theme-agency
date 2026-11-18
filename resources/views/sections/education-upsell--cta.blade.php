@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    /**
     * education-upsell-cta (Wave 4c signature widget): the course/teardown
     * list rendered with a single lead card promoted into a wide conversion
     * banner, so the education upsell reads as one strong invitation rather
     * than three equal cards -- the promoted item is always the first item
     * in the payload (deterministic; §0.1).
     */
    $kicker = data_get($section, 'kicker');
    $heading = data_get($section, 'heading');
    $summary = data_get($section, 'summary');
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->take(50)
        ->values();

    $leadItem = $items->first();
    $restItems = $items->slice(1)->values();
    $leadUrl = PublicUrlSanitizer::sanitize(data_get($leadItem, 'url', data_get($leadItem, 'href')));
    $ctaLabel = data_get($leadItem, 'cta_label', data_get($section, 'cta_label'));
    $leadLabel = data_get($section, 'lead_label');
@endphp

@if ($items->isNotEmpty())
    <section
        id="learn"
        class="ppc-section ppc-section-field"
        data-widget="education-upsell-cta"
    >
        <div class="ppc-section-inner">
            @if (filled($kicker))
                <p class="ppc-kicker">{{ $kicker }}</p>
            @endif

            @if (filled($heading))
                <h2>{{ $heading }}</h2>
            @endif

            @if (filled($summary))
                <p class="ppc-lede">{{ $summary }}</p>
            @endif

            @if ($leadItem !== null)
                <article class="ppc-card ppc-course-card ppc-course-lead">
                    @if (filled($leadLabel))
                        <p class="ppc-chip">{{ $leadLabel }}</p>
                    @endif
                    <h3>
                        @if (filled($leadUrl))
                            <a
                                class="ppc-title-link"
                                href="{{ $leadUrl }}"
                            >
                                {{ data_get($leadItem, 'title', data_get($leadItem, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($leadItem, 'title', data_get($leadItem, 'name', '')) }}
                        @endif
                    </h3>
                    @if (filled(data_get($leadItem, 'summary')))
                        <p>{{ data_get($leadItem, 'summary') }}</p>
                    @endif

                    @if (filled($ctaLabel) && filled($leadUrl))
                        <a
                            class="ppc-button"
                            href="{{ $leadUrl }}"
                        >
                            {{ $ctaLabel }}
                        </a>
                    @endif
                </article>
            @endif

            @if ($restItems->isNotEmpty())
                <div class="ppc-grid">
                    @foreach ($restItems as $item)
                        <article class="ppc-card ppc-course-card">
                            <h3>
                                @if (filled(PublicUrlSanitizer::sanitize(data_get($item, 'url', data_get($item, 'href')))))
                                    <a
                                        class="ppc-title-link"
                                        href="{{ PublicUrlSanitizer::sanitize(data_get($item, 'url', data_get($item, 'href'))) }}"
                                    >
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                @endif
                            </h3>
                            @if (filled(data_get($item, 'summary')))
                                <p>{{ data_get($item, 'summary') }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
