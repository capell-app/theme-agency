<section
    id="newsletter"
    class="ppc-section ppc-section-field"
>
    <div class="ppc-section-inner ppc-split">
        <div>
            <p class="ppc-kicker">{{ __('capell-theme-agency::sections.newsletter.kicker') }}</p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-agency::sections.newsletter.heading')) }}
            </h2>
            <p class="ppc-lede">
                {{ data_get($section, 'summary', __('capell-theme-agency::sections.newsletter.summary')) }}
            </p>
        </div>
        <x-capell::newsletter-form
            :fallback-action="(string) data_get($section, 'action', '#newsletter')"
            class="ppc-form"
        >
            <label for="ppc-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-agency::sections.newsletter.email_label')) }}
            </label>
            <input
                id="ppc-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-agency::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="ppc-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-agency::sections.newsletter.button')) }}
            </button>
            <p class="ppc-meta">{{ __('capell-theme-agency::sections.newsletter.note') }}</p>
        </x-capell::newsletter-form>
    </div>
</section>
