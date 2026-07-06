@php
    // launch-cta-sequence (Wave 4c signature widget): kicker, heading, lede,
    // and the button stagger into view together as the final beat of the
    // page's scroll-staged reveal sequence.
@endphp

<section
    id="cta"
    class="lga-section lga-section-dark lga-sequence-hero"
>
    <div class="lga-section-inner">
        <p class="lga-eyebrow lga-sequence-step" style="--lga-step: 0">
            {{ __('capell-theme-launch-pad::sections.cta.kicker') }}
        </p>
        <h2
            class="lga-sequence-step"
            style="--lga-step: 1"
        >
            {{ data_get($section, 'heading', __('capell-theme-launch-pad::sections.cta.heading')) }}
        </h2>
        <p class="lga-lede lga-sequence-step" style="--lga-step: 2">
            {{ data_get($section, 'summary', __('capell-theme-launch-pad::sections.cta.summary')) }}
        </p>
        <div
            class="lga-actions lga-sequence-step"
            style="--lga-step: 3"
        >
            <a
                class="lga-button"
                href="{{ data_get($section, 'url', '#website-examples') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-launch-pad::sections.cta.button')) }}
            </a>
        </div>
    </div>
</section>
