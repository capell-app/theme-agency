<section
    id="newsletter"
    class="cce-section cce-section-field"
>
    <div class="cce-section-inner cce-split">
        <div>
            <p class="cce-kicker">
                {{ __('capell-theme-creative-culture-editorial::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-creative-culture-editorial::sections.newsletter.heading')) }}
            </h2>
            <p class="cce-lede">
                {{ data_get($section, 'summary', __('capell-theme-creative-culture-editorial::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="cce-form"
        >
            <label for="cce-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-creative-culture-editorial::sections.newsletter.email_label')) }}
            </label>
            <input
                id="cce-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-creative-culture-editorial::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="cce-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-creative-culture-editorial::sections.newsletter.button')) }}
            </button>
            <p class="cce-meta">
                {{ __('capell-theme-creative-culture-editorial::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
