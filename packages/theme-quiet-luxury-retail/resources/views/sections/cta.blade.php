<section class="luxury-section luxury-section-dark">
    <div class="luxury-section-inner">
        <p class="luxury-kicker">
            {{ __('capell-theme-quiet-luxury-retail::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.cta.heading')) }}
        </h2>
        <p class="luxury-lede">
            {{ data_get($section, 'summary', __('capell-theme-quiet-luxury-retail::sections.cta.summary')) }}
        </p>
        <a
            class="luxury-button"
            href="{{ data_get($section, 'url', '/') }}"
        >
            {{ data_get($section, 'label', __('capell-theme-quiet-luxury-retail::sections.cta.button')) }}
        </a>
    </div>
</section>
