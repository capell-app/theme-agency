<section class="outdoor-section outdoor-section-dark">
    <div class="outdoor-section-inner">
        <p class="outdoor-kicker">
            {{ __('capell-theme-outdoor-mission::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-outdoor-mission::sections.cta.heading')) }}
        </h2>
        <p class="outdoor-lede">
            {{ data_get($section, 'summary', __('capell-theme-outdoor-mission::sections.cta.summary')) }}
        </p>
        <a
            class="outdoor-button"
            href="{{ data_get($section, 'url', '/') }}"
        >
            {{ data_get($section, 'label', __('capell-theme-outdoor-mission::sections.cta.button')) }}
        </a>
    </div>
</section>
