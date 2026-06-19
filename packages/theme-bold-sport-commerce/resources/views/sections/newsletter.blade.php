<section
    class="sport-section"
    style="background: var(--sport-field)"
>
    <div class="sport-section-inner sport-split">
        <div>
            <p class="sport-kicker">
                {{ __('capell-theme-bold-sport-commerce::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-bold-sport-commerce::sections.newsletter.heading')) }}
            </h2>
            <p class="sport-lede">
                {{ data_get($section, 'summary', __('capell-theme-bold-sport-commerce::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="post"
            action="{{ data_get($section, 'action', '/') }}"
            class="sport-card"
        >
            <label for="sport-newsletter-email">
                {{ __('capell-theme-bold-sport-commerce::sections.newsletter.email_label') }}
            </label>
            <input
                id="sport-newsletter-email"
                name="email"
                type="email"
                required
            />
            <button
                class="sport-button"
                type="submit"
            >
                {{ __('capell-theme-bold-sport-commerce::sections.newsletter.button') }}
            </button>
        </form>
    </div>
</section>
