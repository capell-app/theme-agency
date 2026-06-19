<section class="editorial-section editorial-section-dark">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-dark-product-system::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-dark-product-system::sections.cta.heading')) }}
        </h2>
        <p class="editorial-lede">
            {{ data_get($section, 'summary', __('capell-theme-dark-product-system::sections.cta.summary')) }}
        </p>
        <a
            class="editorial-button"
            href="{{ data_get($section, 'url', '/') }}"
        >
            {{ data_get($section, 'label', __('capell-theme-dark-product-system::sections.cta.button')) }}
        </a>
    </div>
</section>
