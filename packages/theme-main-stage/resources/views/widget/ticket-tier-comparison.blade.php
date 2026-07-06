@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-main-stage::generic.tickets.kicker'));
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $variant = (string) ($widget->getMeta('variant') ?? 'table');
    $tiers = is_array($widget->getMeta('tiers')) ? $widget->getMeta('tiers') : [];
@endphp

{{--
    `ticket-tier-comparison` — Part 2 §E signature widget, the theme's
    "multi-colour tiers" differentiator (see the programme doc's five-way
    conversion note). Each tier's accent is a CSS custom property
    (`--mst-tier-color`) set inline from the payload's `colorToken` — a
    named design token (e.g. `general`, `pro`, `vip`), never a literal hex
    value, so `ThemeDarkModeParityTest`'s hardcoded-hex scan stays clean and
    dark mode can remap the same token set.

    Two variants (shared `responsive-table-to-cards` primitive pattern):
    `table` (a real comparison table on wide viewports) and `cards`
    (one card per tier, always-cards — useful when a theme wants the bold
    poster card treatment even on desktop). Both markups fall back to a
    stacked one-column layout under the same `@container` narrow-width rule
    the table variant defines, so a table never needs horizontal scroll on
    mobile.

    Payload cap: ticket tiers are a short, curated list (never table's ≤100
    cap in practice, but the same comparison-table primitive still applies).
--}}
<section
    id="tickets"
    class="mst-shell mst-section mst-section-raised"
>
    <div class="mst-section-inner">
        <div class="mst-heading-row">
            <div>
                <p class="mst-eyebrow">{{ __('capell-theme-main-stage::generic.tickets.kicker') }}</p>
                <h2>{{ $heading }}</h2>
                @if ($summary !== '')
                    <p class="mst-lede">{{ $summary }}</p>
                @endif
            </div>
        </div>

        @if ($variant === 'table')
            <div
                class="mst-ticket-table"
                role="table"
            >
                @foreach ($tiers as $tier)
                    <div
                        class="mst-ticket-row"
                        role="row"
                        style="--mst-tier-color: var(--mst-tier-{{ data_get($tier, 'colorToken', 'general') }}, var(--mst-accent));"
                    >
                        <div
                            class="mst-ticket-cell mst-ticket-cell-name"
                            role="cell"
                        >
                            <span
                                class="mst-ticket-swatch"
                                aria-hidden="true"
                            ></span>
                            <h3>{{ data_get($tier, 'name', '') }}</h3>
                        </div>
                        <div
                            class="mst-ticket-cell mst-ticket-cell-price"
                            role="cell"
                        >
                            {{ data_get($tier, 'price', '') }}
                        </div>
                        <div
                            class="mst-ticket-cell mst-ticket-cell-features"
                            role="cell"
                        >
                            <ul>
                                @foreach ((array) data_get($tier, 'features', []) as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div
                            class="mst-ticket-cell mst-ticket-cell-action"
                            role="cell"
                        >
                            <a
                                href="{{ data_get($tier, 'url', '#tickets') }}"
                                class="mst-button"
                                >{{ data_get($tier, 'buttonLabel', 'Get tickets') }}</a
                            >
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mst-ticket-cards">
                @foreach ($tiers as $tier)
                    <article
                        class="mst-ticket-card"
                        style="--mst-tier-color: var(--mst-tier-{{ data_get($tier, 'colorToken', 'general') }}, var(--mst-accent));"
                    >
                        <span
                            class="mst-ticket-swatch"
                            aria-hidden="true"
                        ></span>
                        <h3>{{ data_get($tier, 'name', '') }}</h3>
                        <p class="mst-ticket-card-price">{{ data_get($tier, 'price', '') }}</p>
                        <ul>
                            @foreach ((array) data_get($tier, 'features', []) as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <a
                            href="{{ data_get($tier, 'url', '#tickets') }}"
                            class="mst-button"
                            >{{ data_get($tier, 'buttonLabel', 'Get tickets') }}</a
                        >
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
