<section
    id="newsletter"
    class="dlm-section dlm-section-field"
>
    <div class="dlm-section-inner dlm-split">
        <div>
            <p class="dlm-kicker">
                {{ __('capell-theme-art-paper::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-art-paper::sections.newsletter.heading')) }}
            </h2>
            <p class="dlm-lede">
                {{ data_get($section, 'summary', __('capell-theme-art-paper::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#') }}"
            class="dlm-form"
        >
            <label for="dlm-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-art-paper::sections.newsletter.email_label')) }}
            </label>
            <input
                id="dlm-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-art-paper::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="dlm-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-art-paper::sections.newsletter.button')) }}
            </button>
            <p class="dlm-meta">
                {{ __('capell-theme-art-paper::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
