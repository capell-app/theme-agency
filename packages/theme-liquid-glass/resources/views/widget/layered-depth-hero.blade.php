{{--
    layered-depth-hero (Wave 4c signature widget, §D "glassmorphism
    showcase"): a z-depth layered composition — a backdrop pane, a midground
    glass pane, and a foreground glass pane, each carrying a genuinely
    different `translate`/`scale` resting position so the stack reads as
    real depth even before anything moves (§0.6 "none/minimal/subtle" tiers:
    a STATIC but composed layout, never an absence of design).

    Pointer-parallax is gated to the `energetic` motion tier ONLY, per §0.6
    ("energetic" is the only tier that gets 30-50px parallax) — implemented
    entirely in CSS (no pointer-tracking JS at all, so there is nothing to
    gate incorrectly at runtime): `--layered-depth-hero-parallax-x/-y`
    custom properties default to `0px` and only ever take a non-zero value
    inside a `[data-motion-intensity='energetic'] .layered-depth-hero:hover`
    /`:focus-within` rule (see `resources/css/theme-liquid-glass.css`), which
    itself only lives inside a `@media (prefers-reduced-motion: no-preference)`
    block guarded by `@supports (transform: translate3d(var(--x, 0px), var(--y, 0px), 0))`,
    so a reduced-motion visitor or any non-energetic-tier visitor both see
    the plain static resting composition — the CSS-only idiom already
    established by theme-front-row's `featured-portfolios--parallax`
    variant (a scroll trigger there), applied here as a hover/focus trigger
    on the section itself, which is a real CSS pseudo-class interaction, not
    a JS pointer-move listener.

    Motion-tier gating is double-checked, not single-gated (§0.5): the
    `[data-motion-intensity]` attribute set on `<html>` by
    `theme-foundation/resources/views/app.blade.php` is the CSS selector
    this section's parallax rule keys off, AND the parallax rule itself only
    lives inside the reduced-motion-safe `@media` block, so both conditions
    from the acceptance criteria gate it, not just one.

    Variant contract (see glass-feature-card.blade.php's docblock for why
    this is inline `variant`-key branching): two reachable states via
    payload `variant`:
    - `layered` (default): three-pane depth stack, images optional.
    - `flat-fallback`: same content, single-pane composition — the variant a
      caller can force when they want the depth idiom's copy/structure
      without any pane offset at all (e.g. a narrow container).
--}}
@php
    $eyebrow = (string) ($widget->getMeta('eyebrow') ?? __('capell-theme-liquid-glass::generic.layered_depth_hero_eyebrow'));
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-liquid-glass::generic.layered_depth_hero_heading'));
    $summary = $widget->getMeta('summary') ?? __('capell-theme-liquid-glass::generic.layered_depth_hero_summary');
    $panes = is_array($widget->getMeta('panes')) ? $widget->getMeta('panes') : [];
    $variant = (string) ($widget->getMeta('variant') ?? 'layered');
@endphp

<section
    @class ([
        'layered-depth-hero relative px-5 py-16 sm:px-6 lg:px-8',
        'layered-depth-hero--flat' => $variant === 'flat-fallback',
    ])
    data-variant="{{ $variant }}"
>
    <div class="relative mx-auto max-w-7xl">
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

        <div class="layered-depth-hero-stack mt-10 grid">
            @foreach ($panes as $pane)
                @php
                    $depthIndex = $loop->index;
                    $paneImage = data_get($pane, 'image');
                @endphp

                <div
                    class="glass-surface layered-depth-hero-pane"
                    style="--layered-depth-hero-pane-index: {{ $depthIndex }}"
                    data-pane-depth="{{ $depthIndex }}"
                >
                    @if (filled($paneImage))
                        <img
                            src="{{ $paneImage }}"
                            alt="{{ data_get($pane, 'imageAlt', '') }}"
                            loading="lazy"
                            decoding="async"
                            width="640"
                            height="420"
                            class="aspect-[16/10] w-full object-cover"
                        />
                    @endif

                    @if (filled(data_get($pane, 'label')))
                        <p class="layered-depth-hero-pane-label">
                            {{ data_get($pane, 'label') }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
