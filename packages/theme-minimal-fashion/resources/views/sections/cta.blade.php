<section class="fashion-section fashion-section-dark">
    <div class="fashion-section-inner">
        <p class="fashion-kicker">
            {{ __('capell-theme-minimal-fashion::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-minimal-fashion::sections.cta.heading')) }}
        </h2>
        <p class="fashion-lede">
            {{ data_get($section, 'summary', __('capell-theme-minimal-fashion::sections.cta.summary')) }}
        </p>
        <a
            class="fashion-button"
            href="{{ data_get($section, 'url', '/') }}"
        >
            {{ data_get($section, 'label', __('capell-theme-minimal-fashion::sections.cta.button')) }}
        </a>
    </div>
</section>
