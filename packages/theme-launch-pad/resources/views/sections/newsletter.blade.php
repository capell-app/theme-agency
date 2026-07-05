<section
    id="newsletter"
    class="lga-section lga-section-field"
>
    <div class="lga-section-inner lga-split">
        <div>
            <p class="lga-eyebrow">
                {{ __('capell-theme-launch-pad::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-launch-pad::sections.newsletter.heading')) }}
            </h2>
            <p class="lga-lede">
                {{ data_get($section, 'summary', __('capell-theme-launch-pad::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="lga-form"
        >
            <label for="lga-newsletter-email">
                {{ __('capell-theme-launch-pad::sections.newsletter.email_label') }}
            </label>
            <input
                id="lga-newsletter-email"
                name="email"
                type="email"
                required
            />
            <button
                class="lga-button"
                type="submit"
            >
                {{ __('capell-theme-launch-pad::sections.newsletter.button') }}
            </button>
        </form>
    </div>
</section>
