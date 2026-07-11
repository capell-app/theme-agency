@php
    /**
     * awarded-profiles-spotlight (Wave 4c signature widget): gold/silver/
     * bronze tiers for the year's top awarded profiles, presented as a
     * spotlight row rather than the base grid -- the first entry is the
     * gold spotlight (larger plate), the next two are silver/bronze runners
     * up. Tier is read from payload when supplied (`tier: gold|silver|
     * bronze`); otherwise it falls back to position order, deterministic
     * either way -- never randomised (§0.1).
     */
    $heading = data_get($section, 'heading', __('capell-theme-agency::sections.updates.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-agency::sections.updates.summary'));
    $ctaLabel = data_get($section, 'label', __('capell-theme-agency::sections.updates.button'));
    $ctaUrl = data_get($section, 'url', '#awards');
    $tierOrder = ['gold', 'silver', 'bronze'];
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-agency::sections.updates.release_title'), 'summary' => __('capell-theme-agency::sections.updates.release_summary')],
        ['title' => __('capell-theme-agency::sections.updates.beta_title'), 'summary' => __('capell-theme-agency::sections.updates.beta_summary')],
    ]))->take(20)->values();
@endphp

<section
    id="awards"
    class="ppc-section ppc-section-dark"
    data-widget="awarded-profiles-spotlight"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ __('capell-theme-agency::sections.updates.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="ppc-lede">{{ $summary }}</p>
        <a
            class="ppc-button"
            href="{{ $ctaUrl }}"
        >
            {{ $ctaLabel }}
        </a>

        <div
            class="ppc-spotlight-row"
            role="list"
        >
            @foreach ($items as $item)
                @php
                    $tier = data_get($item, 'tier');
                    $tier = in_array($tier, $tierOrder, true) ? $tier : ($tierOrder[$loop->index] ?? 'bronze');
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $award = data_get($item, 'award', __('capell-theme-agency::sections.updates.default_award'));
                    $tierLabel = match ($tier) {
                        'gold' => __('capell-theme-agency::sections.spotlight.tier_gold'),
                        'silver' => __('capell-theme-agency::sections.spotlight.tier_silver'),
                        default => __('capell-theme-agency::sections.spotlight.tier_bronze'),
                    };
                @endphp

                <article
                    class="ppc-card ppc-spotlight-card ppc-spotlight-{{ $tier }}"
                    role="listitem"
                >
                    <figure class="ppc-plate">
                        <div class="ppc-plate-frame">
                            <span
                                class="ppc-badge ppc-spotlight-badge-{{ $tier }}"
                            >
                                {{ $tierLabel }} &middot; {{ $award }}
                            </span>

                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
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
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
