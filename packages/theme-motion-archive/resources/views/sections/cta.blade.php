<section
    id="cta"
    class="mva-section mva-section-raised"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-motion-archive::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-motion-archive::sections.cta.heading')) }}
        </h2>
        <p class="mva-lede">
            {{ data_get($section, 'summary', __('capell-theme-motion-archive::sections.cta.summary')) }}
        </p>
        <div class="mva-actions">
            <a
                class="mva-button"
                href="{{ data_get($section, 'url', '/') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-motion-archive::sections.cta.button')) }}
            </a>
        </div>
    </div>
</section>
