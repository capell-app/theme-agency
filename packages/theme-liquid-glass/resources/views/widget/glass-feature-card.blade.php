{{--
    glass-feature-card (Wave 4c signature widget, §D "glassmorphism showcase"):
    a feature card built from the theme's signature glassmorphic treatment —
    backdrop-filter blur, a translucent surface, and a layered border
    highlight (a bright inset top edge plus a dim inset bottom edge, so the
    card reads as catching light from above like real glass).

    Variant contract: this is a layout-native widget, so it has no
    `VariantViewSectionRenderer` sidecar view (that mechanism is
    classic-theme-only — see LiquidGlassThemeServiceProvider's docblocks).
    Instead, like every other bespoke widget in this theme, the payload's
    `variant` key branches presentation inline via `@class`/conditional
    markup, mirroring the `variant` key `contentListingSection()` already
    seeds in `LiquidGlassDemoContent`. Two genuinely distinct, reachable
    states:
    - `standard` (default): a single translucent card, border highlight, and
      optional metric.
    - `spotlight`: a taller card with a stronger overlay tint and a larger
      heading, meant to lead a row of `standard` cards.
--}}
@php
    $eyebrow = (string) ($widget->getMeta('eyebrow') ?? __('capell-theme-liquid-glass::generic.glass_feature_card_eyebrow'));
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-liquid-glass::generic.glass_feature_card_heading'));
    $summary = $widget->getMeta('summary') ?? __('capell-theme-liquid-glass::generic.glass_feature_card_summary');
    $items = is_array($widget->getMeta('items')) ? $widget->getMeta('items') : [];
    $variant = (string) ($widget->getMeta('variant') ?? 'standard');
@endphp

<section class="px-5 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="max-w-3xl">
            @if ($eyebrow !== '')
                <p class="text-xs font-black tracking-[0.08em] text-(--theme-primary) uppercase">
                    {{ $eyebrow }}
                </p>
            @endif

            <h2
                class="mt-4 text-4xl leading-tight font-black text-balance text-(--theme-foreground) sm:text-5xl"
            >
                {{ $heading }}
            </h2>

            @if ($summary)
                <p class="mt-5 text-lg leading-8 text-(--theme-foreground)/75">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="glass-surface-grid mt-10 grid md:grid-cols-3">
            @foreach ($items as $item)
                @php
                    $cardVariant = (string) data_get($item, 'variant', $variant);
                @endphp

                <article
                    @class ([
                        'glass-surface glass-surface-card glass-feature-card overflow-hidden border border-(--theme-foreground)/10 bg-(--theme-surface)/80',
                        'glass-feature-card--spotlight' => $cardVariant === 'spotlight',
                    ])
                    data-variant="{{ $cardVariant }}"
                >
                    <p class="text-sm font-bold tracking-wide text-(--theme-primary) uppercase">
                        {{ data_get($item, 'eyebrow', '') }}
                    </p>
                    <h3
                        class="mt-3 leading-tight font-black text-(--theme-foreground)"
                    >
                        {{ data_get($item, 'title', '') }}
                    </h3>
                    <p class="mt-4 text-sm leading-7 text-(--theme-foreground)/70">
                        {{ data_get($item, 'summary', '') }}
                    </p>

                    @if (filled(data_get($item, 'metric')))
                        <p class="mt-5 text-3xl font-black text-(--theme-primary)">
                            {{ data_get($item, 'metric') }}
                            <span
                                class="block text-xs font-bold tracking-wide text-(--theme-foreground)/55 uppercase"
                            >
                                {{ data_get($item, 'metricLabel', '') }}
                            </span>
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
