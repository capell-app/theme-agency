<section
    id="newsletter"
    class="ppc-section ppc-section-field"
>
    <div class="ppc-section-inner ppc-split">
        <div>
            @if (filled(data_get($section, 'kicker')))
                <p class="ppc-kicker">{{ data_get($section, 'kicker') }}</p>
            @endif

            @if (filled(data_get($section, 'heading')))
                <h2>{{ data_get($section, 'heading') }}</h2>
            @endif

            @if (filled(data_get($section, 'summary')))
                <p class="ppc-lede">{{ data_get($section, 'summary') }}</p>
            @endif
        </div>
        <x-capell::newsletter-form
            :fallback-action="(string) data_get($section, 'action', '')"
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
            @if (filled(data_get($section, 'note')))
                <p class="ppc-meta">{{ data_get($section, 'note') }}</p>
            @endif
        </x-capell::newsletter-form>
    </div>
</section>
