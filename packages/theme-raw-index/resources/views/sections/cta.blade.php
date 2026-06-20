<section class="editorial-section editorial-section-dark">
    <div class="editorial-section-inner">
        <p class="editorial-kicker">
            {{ __('capell-theme-raw-index::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-raw-index::sections.cta.heading')) }}
        </h2>
        <p class="editorial-lede">
            {{ data_get($section, 'summary', __('capell-theme-raw-index::sections.cta.summary')) }}
        </p>
        <a
            class="editorial-button"
            href="{{ data_get($section, 'url', '/') }}"
        >
            {{ data_get($section, 'label', __('capell-theme-raw-index::sections.cta.button')) }}
        </a>
    </div>
</section>
