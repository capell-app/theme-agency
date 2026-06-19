<section class="editorial-section editorial-section-dark">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-quiet-web-gallery::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-web-gallery::sections.cta.heading')) }}
        </h2>
        <p class="editorial-lede">
            {{ data_get($section, 'summary', __('capell-theme-quiet-web-gallery::sections.cta.summary')) }}
        </p>
        <a
            class="editorial-button"
            href="{{ data_get($section, 'url', '/') }}"
        >
            {{ data_get($section, 'label', __('capell-theme-quiet-web-gallery::sections.cta.button')) }}
        </a>
    </div>
</section>
