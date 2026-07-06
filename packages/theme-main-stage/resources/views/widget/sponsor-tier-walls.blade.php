@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-main-stage::generic.sponsors.kicker'));
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $variant = (string) ($widget->getMeta('variant') ?? 'tiers');
    $tiers = is_array($widget->getMeta('tiers')) ? $widget->getMeta('tiers') : [];
@endphp

{{--
    `sponsor-tier-walls` — Part 2 §E signature widget, "multi-colour tiers"
    differentiator applied to sponsor logos. Each tier gets its own accent
    token (`--mst-tier-color`, resolved from a named `colorToken` the same
    way `ticket-tier-comparison` does — never a literal hex) so, e.g., a
    "headline" tier's logos sit in a bolder frame than a "supporting" tier's.

    Two variants: `tiers` (one row per tier, largest logos first) and
    `single-row` (all sponsors flattened into one even wall — used when a
    theme wants a quieter, less hierarchical sponsor presentation).

    Payload cap: sponsor logos <= 50 total across all tiers (§0.3).
--}}
<section
    id="sponsors"
    class="mst-shell mst-section mst-section-raised"
>
    <div class="mst-section-inner">
        <div class="mst-heading-row">
            <div>
                <p class="mst-eyebrow">{{ __('capell-theme-main-stage::generic.sponsors.kicker') }}</p>
                <h2>{{ $heading }}</h2>
                @if ($summary !== '')
                    <p class="mst-lede">{{ $summary }}</p>
                @endif
            </div>
        </div>

        <div
            class="mst-sponsor-walls mst-sponsor-walls--{{ $variant === 'single-row' ? 'single-row' : 'tiers' }}"
        >
            @foreach ($tiers as $tier)
                <div
                    class="mst-sponsor-tier"
                    style="--mst-tier-color: var(--mst-tier-{{ data_get($tier, 'colorToken', 'general') }}, var(--mst-accent));"
                >
                    @if ($variant !== 'single-row')
                        <p class="mst-sponsor-tier-label">{{ data_get($tier, 'label', '') }}</p>
                    @endif

                    <div class="mst-sponsor-logo-row">
                        @foreach ((array) data_get($tier, 'sponsors', []) as $sponsor)
                            <a
                                href="{{ data_get($sponsor, 'url', '#') }}"
                                class="mst-sponsor-logo"
                            >
                                @if (data_get($sponsor, 'logo'))
                                    <img
                                        src="{{ data_get($sponsor, 'logo') }}"
                                        alt="{{ data_get($sponsor, 'name', '') }}"
                                        loading="lazy"
                                    />
                                @else
                                    <span
                                        >{{ data_get($sponsor, 'name', '') }}</span
                                    >
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
