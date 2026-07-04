<section
    id="newsletter"
    class="mcf-section"
>
    <div class="mcf-section-inner">
        <p class="mcf-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-minimal-curation-feed::sections.newsletter.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-minimal-curation-feed::sections.newsletter.heading')) }}
        </h2>
        <p class="mcf-lede">
            {{ data_get($section, 'summary', __('capell-theme-minimal-curation-feed::sections.newsletter.summary')) }}
        </p>

        <form
            method="get"
            action="{{ data_get($section, 'action', '#') }}"
            class="mcf-form"
        >
            <div class="mcf-form-field">
                <label for="mcf-newsletter-email">
                    {{ data_get($section, 'emailLabel', data_get($section, 'email_label', __('capell-theme-minimal-curation-feed::sections.newsletter.email_label'))) }}
                </label>
                <input
                    id="mcf-newsletter-email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    placeholder="{{ __('capell-theme-minimal-curation-feed::sections.newsletter.placeholder') }}"
                    required
                />
            </div>
            <button
                class="mcf-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-minimal-curation-feed::sections.newsletter.button')) }}
            </button>
            <p class="mcf-tiny mcf-form-note">
                {{ __('capell-theme-minimal-curation-feed::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
