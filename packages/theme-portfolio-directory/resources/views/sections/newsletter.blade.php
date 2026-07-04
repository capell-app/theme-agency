<section
    id="newsletter"
    class="pfd-section pfd-section-field"
>
    <div class="pfd-section-inner pfd-split">
        <div>
            <p class="pfd-eyebrow">
                {{ data_get($section, 'eyebrow', __('capell-theme-portfolio-directory::sections.newsletter.eyebrow')) }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-portfolio-directory::sections.newsletter.heading')) }}
            </h2>
            <p class="pfd-lede">
                {{ data_get($section, 'summary', __('capell-theme-portfolio-directory::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#') }}"
            class="pfd-form"
        >
            <label for="pfd-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-portfolio-directory::sections.newsletter.email_label')) }}
            </label>
            <input
                id="pfd-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-portfolio-directory::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="pfd-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-portfolio-directory::sections.newsletter.button')) }}
            </button>
            <p class="pfd-form-note">
                {{ __('capell-theme-portfolio-directory::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
