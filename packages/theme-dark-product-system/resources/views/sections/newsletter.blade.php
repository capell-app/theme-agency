<section
    id="newsletter"
    class="dps-section dps-section-field"
>
    <div class="dps-section-inner dps-split">
        <div>
            <p class="dps-eyebrow">
                {{ __('capell-theme-dark-product-system::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-dark-product-system::sections.newsletter.heading')) }}
            </h2>
            <p class="dps-lede">
                {{ data_get($section, 'summary', __('capell-theme-dark-product-system::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="dps-form"
        >
            <label for="dps-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-dark-product-system::sections.newsletter.email_label')) }}
            </label>
            <input
                id="dps-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-dark-product-system::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="dps-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-dark-product-system::sections.newsletter.button')) }}
            </button>
            <p class="dps-form-note">
                {{ __('capell-theme-dark-product-system::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
