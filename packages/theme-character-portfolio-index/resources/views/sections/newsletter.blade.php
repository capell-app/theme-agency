<section
    id="newsletter"
    class="cpi-section cpi-section-field"
>
    <div class="cpi-section-inner cpi-split">
        <div>
            <p class="cpi-kicker">
                {{ __('capell-theme-character-portfolio-index::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-character-portfolio-index::sections.newsletter.heading')) }}
            </h2>
            <p class="cpi-lede">
                {{ data_get($section, 'summary', __('capell-theme-character-portfolio-index::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="cpi-form"
        >
            <label for="cpi-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-character-portfolio-index::sections.newsletter.email_label')) }}
            </label>
            <input
                id="cpi-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-character-portfolio-index::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="cpi-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-character-portfolio-index::sections.newsletter.button')) }}
            </button>
            <p class="cpi-form-note">
                {{ __('capell-theme-character-portfolio-index::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
