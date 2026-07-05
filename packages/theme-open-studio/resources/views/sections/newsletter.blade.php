<section
    id="newsletter"
    class="csp-section csp-section-field"
>
    <div class="csp-section-inner csp-split">
        <div>
            <p class="csp-kicker">
                {{ __('capell-theme-open-studio::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-open-studio::sections.newsletter.heading')) }}
            </h2>
            <p class="csp-lede">
                {{ data_get($section, 'summary', __('capell-theme-open-studio::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="csp-form"
        >
            <label for="csp-newsletter-email">
                {{ __('capell-theme-open-studio::sections.newsletter.email_label') }}
            </label>
            <input
                id="csp-newsletter-email"
                name="email"
                type="email"
                required
            />
            <button
                class="csp-button"
                type="submit"
            >
                {{ __('capell-theme-open-studio::sections.newsletter.button') }}
            </button>
        </form>
    </div>
</section>
