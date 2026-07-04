<section
    class="gcm-section gcm-section-tinted"
    id="newsletter"
>
    <div class="gcm-section-inner gcm-split">
        <div>
            <p class="gcm-kicker">
                {{ __('capell-theme-global-culture-magazine::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-global-culture-magazine::sections.newsletter.heading')) }}
            </h2>
            <p class="gcm-lede">
                {{ data_get($section, 'summary', __('capell-theme-global-culture-magazine::sections.newsletter.summary')) }}
            </p>
        </div>
        {{--
            GET with an in-page default: no newsletter endpoint ships with the
            theme, so posting anywhere would 419/405 on a real install.
        --}}
        <form
            method="get"
            action="{{ data_get($section, 'action', '#') }}"
            class="gcm-form"
        >
            <label for="gcm-newsletter-email">
                {{ __('capell-theme-global-culture-magazine::sections.newsletter.email_label') }}
            </label>
            <input
                id="gcm-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-global-culture-magazine::sections.newsletter.email_placeholder') }}"
                required
            />
            <button
                class="gcm-button"
                type="submit"
            >
                {{ data_get($section, 'label', __('capell-theme-global-culture-magazine::sections.newsletter.button')) }}
            </button>
            <p class="gcm-form-note">
                {{ __('capell-theme-global-culture-magazine::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
