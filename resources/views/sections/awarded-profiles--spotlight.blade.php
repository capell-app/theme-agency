@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    /**
     * awarded-profiles-spotlight (Wave 4c signature widget): gold/silver/
     * bronze tiers for the year's top awarded profiles, presented as a
     * spotlight row rather than the base grid -- the first entry is the
     * gold spotlight (larger plate), the next two are silver/bronze runners
     * up. Tier is read from payload when supplied (`tier: gold|silver|
     * bronze`); otherwise the position order sets only the plate styling,
     * and the badge prints just the saved tier and award, never an invented
     * tier name.
     */
    $heading = data_get($section, 'heading');
    $summary = data_get($section, 'summary');
    $kicker = data_get($section, 'kicker');
    $ctaLabel = data_get($section, 'label');
    $ctaUrl = PublicUrlSanitizer::sanitize(data_get($section, 'url'));
    $tierOrder = ['gold', 'silver', 'bronze'];
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->take(20)
        ->values();
@endphp

@if ($items->isNotEmpty())
    <section
        id="awards"
        class="ppc-section ppc-section-dark"
        data-widget="awarded-profiles-spotlight"
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

            @if (filled($ctaLabel) && filled($ctaUrl))
                <a
                    class="ppc-button"
                    href="{{ $ctaUrl }}"
                >
                    {{ $ctaLabel }}
                </a>
            @endif

            <div
                class="ppc-spotlight-row"
                role="list"
            >
                @foreach ($items as $item)
                    @php
                        $savedTier = data_get($item, 'tier');
                        $hasTier = in_array($savedTier, $tierOrder, true);
                        $tier = $hasTier ? $savedTier : ($tierOrder[$loop->index] ?? 'bronze');
                        $itemImage = PublicUrlSanitizer::sanitize(data_get($item, 'image', data_get($item, 'imageUrl')));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = PublicUrlSanitizer::sanitize(data_get($item, 'url', data_get($item, 'href')));
                        $award = data_get($item, 'award');
                        $tierLabel = ! $hasTier ? null : match ($tier) {
                            'gold' => __('capell-theme-agency::sections.spotlight.tier_gold'),
                            'silver' => __('capell-theme-agency::sections.spotlight.tier_silver'),
                            default => __('capell-theme-agency::sections.spotlight.tier_bronze'),
                        };
                    @endphp

                    <article
                        @if (filled(data_get($item, 'id'))) id="{{ data_get($item, 'id') }}" @endif
                        class="ppc-card ppc-spotlight-card ppc-spotlight-{{ $tier }}"
                        role="listitem"
                    >
                        <figure class="ppc-plate">
                            <div class="ppc-plate-frame">
                                @if (filled($tierLabel) || filled($award))
                                    <span
                                        class="ppc-badge ppc-spotlight-badge-{{ $tier }}"
                                    >
                                        {{ $tierLabel }}
                                        @if (filled($tierLabel) && filled($award))
                                            &middot;
                                        @endif

                                        {{ $award }}
                                    </span>
                                @endif

                                @if (filled($itemImage))
                                    <x-capell::image-source
                                        :image="$itemImage"
                                        alt="{{ $itemAlt }}"
                                        loading="lazy"
                                        decoding="async"
                                        width="400"
                                        height="300"
                                        class="ppc-plate-media"
                                    />
                                @else
                                    <div
                                        class="ppc-plate-media ppc-plate-media-empty"
                                        aria-hidden="true"
                                    ></div>
                                @endif
                            </div>
                        </figure>
                        <h3>
                            @if (filled($itemUrl))
                                <a
                                    class="ppc-title-link"
                                    href="{{ $itemUrl }}"
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
        </div>
    </section>
@endif
