<section
    class="luxury-section"
    style="background: var(--luxury-field)"
>
    <div class="luxury-section-inner luxury-split">
        <div>
            <p class="luxury-kicker">
                {{ __('capell-theme-quiet-luxury-retail::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.newsletter.heading')) }}
            </h2>
            <p class="luxury-lede">
                {{ data_get($section, 'summary', __('capell-theme-quiet-luxury-retail::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="post"
            action="{{ data_get($section, 'action', '/') }}"
            class="luxury-card"
        >
            <label for="luxury-newsletter-email">
                {{ __('capell-theme-quiet-luxury-retail::sections.newsletter.email_label') }}
            </label>
            <input
                id="luxury-newsletter-email"
                name="email"
                type="email"
                required
            />
            <button
                class="luxury-button"
                type="submit"
            >
                {{ __('capell-theme-quiet-luxury-retail::sections.newsletter.button') }}
            </button>
        </form>
    </div>
</section>
