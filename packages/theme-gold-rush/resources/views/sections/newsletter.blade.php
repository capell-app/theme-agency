<section
    id="newsletter"
    class="sbs-section sbs-section-field"
>
    <div class="sbs-section-inner sbs-hero-grid">
        <div>
            <p class="sbs-kicker">
                {{ __('capell-theme-gold-rush::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-gold-rush::sections.newsletter.heading')) }}
            </h2>
            <p class="sbs-lede">
                {{ data_get($section, 'summary', __('capell-theme-gold-rush::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="sbs-form"
        >
            <label for="sbs-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-gold-rush::sections.newsletter.email_label')) }}
            </label>
            <input
                id="sbs-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-gold-rush::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="sbs-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-gold-rush::sections.newsletter.button')) }}
            </button>
            <p class="sbs-meta">
                {{ __('capell-theme-gold-rush::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
