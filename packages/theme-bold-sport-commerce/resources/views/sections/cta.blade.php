<section class="sport-section sport-section-dark">
    <div class="sport-section-inner">
        <p class="sport-kicker">
            {{ __('capell-theme-bold-sport-commerce::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-bold-sport-commerce::sections.cta.heading')) }}
        </h2>
        <p class="sport-lede">
            {{ data_get($section, 'summary', __('capell-theme-bold-sport-commerce::sections.cta.summary')) }}
        </p>
        <a
            class="sport-button"
            href="{{ data_get($section, 'url', '/') }}"
        >
            {{ data_get($section, 'label', __('capell-theme-bold-sport-commerce::sections.cta.button')) }}
        </a>
    </div>
</section>
