@php
    /**
     * accreditation-insurance-strips — trust badges/certifications strip.
     *
     * Two variants (theme-bar criterion 3), branched on payload `variant`:
     * "default" (logo-style badge row) and "detailed" (badge plus a short
     * one-line credential summary under each, for themes/pages that want
     * more than a bare logo wall).
     */
    $heading = $widget->getMeta('heading', __('capell-theme-call-out::sections.accreditations.heading'));
    $variant = $widget->getMeta('variant', 'default');
    $badges = collect($widget->getMeta('badges', []))->take(20);
@endphp

<section
    id="accreditation-insurance-strips"
    class="rco-shell rco-section rco-section--compact"
    data-widget="accreditation-insurance-strips"
    data-variant="{{ $variant }}"
>
    <div class="rco-section-inner">
        <p class="rco-eyebrow">{{ $heading }}</p>

        <ul
            class="rco-accreditation-strip rco-accreditation-strip--{{ $variant }}"
        >
            @foreach ($badges as $badge)
                <li class="rco-accreditation-badge">
                    @if (filled(data_get($badge, 'logo')))
                        <img
                            src="{{ data_get($badge, 'logo') }}"
                            alt="{{ data_get($badge, 'name', '') }}"
                            loading="lazy"
                            decoding="async"
                            class="rco-accreditation-logo"
                        />
                    @else
                        <span class="rco-accreditation-name">
                            {{ data_get($badge, 'name', '') }}
                        </span>
                    @endif

                    @if ($variant === 'detailed' && filled(data_get($badge, 'summary')))
                        <span class="rco-accreditation-summary">
                            {{ data_get($badge, 'summary') }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
