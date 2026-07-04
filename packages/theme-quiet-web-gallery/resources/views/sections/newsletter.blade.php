<section
    id="newsletter"
    class="qwg-section qwg-section-field"
>
    <div class="qwg-section-inner qwg-split">
        <div>
            <p class="qwg-kicker">
                {{ __('capell-theme-quiet-web-gallery::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-quiet-web-gallery::sections.newsletter.heading')) }}
            </h2>
            <p class="qwg-lede">
                {{ data_get($section, 'summary', __('capell-theme-quiet-web-gallery::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="qwg-form"
        >
            <label for="qwg-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-quiet-web-gallery::sections.newsletter.email_label')) }}
            </label>
            <input
                id="qwg-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-quiet-web-gallery::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="qwg-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-quiet-web-gallery::sections.newsletter.button')) }}
            </button>
            <p class="qwg-form-note">
                {{ __('capell-theme-quiet-web-gallery::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
