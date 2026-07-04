<section
    id="newsletter"
    class="csp-section csp-section-field"
>
    <div class="csp-section-inner csp-split">
        <div>
            <p class="csp-kicker">
                {{ __('capell-theme-case-study-platform::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-case-study-platform::sections.newsletter.heading')) }}
            </h2>
            <p class="csp-lede">
                {{ data_get($section, 'summary', __('capell-theme-case-study-platform::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="csp-form"
        >
            <label for="csp-newsletter-email">
                {{ __('capell-theme-case-study-platform::sections.newsletter.email_label') }}
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
                {{ __('capell-theme-case-study-platform::sections.newsletter.button') }}
            </button>
        </form>
    </div>
</section>
