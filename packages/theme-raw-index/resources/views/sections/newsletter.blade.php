<section
    id="newsletter"
    class="rwi-section rwi-section-field"
>
    <div class="rwi-section-inner">
        <p class="rwi-kicker">
            {{ __('capell-theme-raw-index::sections.newsletter.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-raw-index::sections.newsletter.heading')) }}
        </h2>
        <p class="rwi-lede">
            {{ data_get($section, 'summary', __('capell-theme-raw-index::sections.newsletter.summary')) }}
        </p>

        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="rwi-form"
            style="margin-top: 1.5rem; max-width: 26rem"
        >
            <label for="rwi-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-raw-index::sections.newsletter.email_label')) }}
            </label>
            <input
                id="rwi-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-raw-index::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="rwi-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-raw-index::sections.newsletter.button')) }}
            </button>
            <p class="rwi-meta">
                {{ __('capell-theme-raw-index::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
