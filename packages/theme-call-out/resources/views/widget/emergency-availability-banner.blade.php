@php
    /**
     * emergency-availability-banner — Call Out's headline "state-driven
     * urgency" mechanic (Part 2 §E / Five-way conversion differentiation:
     * "call-out = state-driven urgency (green/amber/red), bold numbers").
     *
     * `state` is an EDITORIAL payload field set by whoever authors the
     * widget — never detected or polled at render time (§0.2 live-state
     * policy) — one of "open" | "after-hours" | "closed", each with its own
     * colour token (green/amber/red, via `--rco-state-*` CSS custom
     * properties, never hardcoded hex) and matching response-time copy.
     *
     * Two variants (theme-bar criterion 3), branched on a payload `variant`
     * key since layout-native themes have no `VariantViewSectionRenderer`
     * sidecar-view seam (see `LiquidGlassThemeServiceProvider`'s and this
     * theme's provider docblock for why): "default" (full banner with
     * response-time copy) and "compact" (single-line strip, no response-time
     * paragraph) — used when this widget repeats lower on a page (e.g. the
     * contact surface) and a full-height banner would be redundant with the
     * homepage's.
     */
    $state = $widget->getMeta('state', 'open');
    $variant = $widget->getMeta('variant', 'default');
    $isCompact = $variant === 'compact';
    $responseTimeOverride = $widget->getMeta('responseTime');

    $stateCopy = [
        'open' => [
            'label' => __('capell-theme-call-out::sections.availability.state_open_label'),
            'responseTime' => __('capell-theme-call-out::sections.availability.state_open_response_default'),
        ],
        'after-hours' => [
            'label' => __('capell-theme-call-out::sections.availability.state_after_hours_label'),
            'responseTime' => __('capell-theme-call-out::sections.availability.state_after_hours_response_default'),
        ],
        'closed' => [
            'label' => __('capell-theme-call-out::sections.availability.state_closed_label'),
            'responseTime' => __('capell-theme-call-out::sections.availability.state_closed_response_default'),
        ],
    ];

    $resolvedState = array_key_exists($state, $stateCopy) ? $state : 'open';
    $copy = $stateCopy[$resolvedState];

    if (filled($responseTimeOverride)) {
        $copy['responseTime'] = $responseTimeOverride;
    }

    $phoneNumber = $widget->getMeta('phoneNumber', '');
@endphp

<section
    id="emergency-availability-banner"
    class="rco-shell rco-section rco-availability-banner rco-availability-banner--{{ $resolvedState }} @if ($isCompact) rco-availability-banner--compact @endif"
    data-widget="emergency-availability-banner"
    data-availability-state="{{ $resolvedState }}"
    role="status"
>
    <div class="rco-section-inner rco-availability-banner-inner">
        <span
            class="rco-availability-dot"
            aria-hidden="true"
        ></span>
        <div class="rco-availability-copy">
            <p class="rco-availability-label">{{ $copy['label'] }}</p>

            @unless ($isCompact)
                <p class="rco-availability-response">{{ $copy['responseTime'] }}</p>
            @endunless
        </div>

        @if (filled($phoneNumber))
            <a
                href="tel:{{ $phoneNumber }}"
                class="rco-btn rco-btn-inverse rco-availability-cta"
            >
                {{ __('capell-theme-call-out::sections.availability.call_now') }} {{ $phoneNumber }}
            </a>
        @endif
    </div>
</section>
