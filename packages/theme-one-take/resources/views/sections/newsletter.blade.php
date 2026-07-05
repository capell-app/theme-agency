<section
    id="newsletter"
    class="ops-section ops-section-field"
>
    <div class="ops-section-inner ops-split">
        <div>
            <p class="ops-kicker">
                {{ __('capell-theme-one-take::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-one-take::sections.newsletter.heading')) }}
            </h2>
            <p class="ops-lede">
                {{ data_get($section, 'summary', __('capell-theme-one-take::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#') }}"
            class="ops-form"
        >
            <label for="ops-newsletter-email">
                {{ __('capell-theme-one-take::sections.newsletter.email_label') }}
            </label>
            <input
                id="ops-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-one-take::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="ops-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-one-take::sections.newsletter.button')) }}
            </button>
            <p class="ops-meta">
                {{ __('capell-theme-one-take::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
