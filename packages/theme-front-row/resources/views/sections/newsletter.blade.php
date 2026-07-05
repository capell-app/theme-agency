<section
    id="newsletter"
    class="ppc-section ppc-section-field"
>
    <div class="ppc-section-inner ppc-split">
        <div>
            <p class="ppc-kicker">
                {{ __('capell-theme-front-row::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-front-row::sections.newsletter.heading')) }}
            </h2>
            <p class="ppc-lede">
                {{ data_get($section, 'summary', __('capell-theme-front-row::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="ppc-form"
        >
            <label for="ppc-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-front-row::sections.newsletter.email_label')) }}
            </label>
            <input
                id="ppc-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-front-row::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="ppc-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-front-row::sections.newsletter.button')) }}
            </button>
            <p class="ppc-meta">
                {{ __('capell-theme-front-row::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
