<section
    id="cta"
    class="lga-section lga-section-dark"
>
    <div class="lga-section-inner">
        <p class="lga-eyebrow">
            {{ __('capell-theme-landing-gallery::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-landing-gallery::sections.cta.heading')) }}
        </h2>
        <p class="lga-lede">
            {{ data_get($section, 'summary', __('capell-theme-landing-gallery::sections.cta.summary')) }}
        </p>
        <div class="lga-actions">
            <a
                class="lga-button"
                href="{{ data_get($section, 'url', '#website-examples') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-landing-gallery::sections.cta.button')) }}
            </a>
        </div>
    </div>
</section>
