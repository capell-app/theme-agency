<section class="product-section product-section-dark">
    <div class="product-section-inner">
        <p class="product-kicker">
            {{ __('capell-theme-premium-product-story::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-premium-product-story::sections.cta.heading')) }}
        </h2>
        <p class="product-lede">
            {{ data_get($section, 'summary', __('capell-theme-premium-product-story::sections.cta.summary')) }}
        </p>
        <a
            class="product-button"
            href="{{ data_get($section, 'url', '/') }}"
        >
            {{ data_get($section, 'label', __('capell-theme-premium-product-story::sections.cta.button')) }}
        </a>
    </div>
</section>
