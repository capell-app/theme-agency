<section
    class="fashion-section"
    style="background: var(--fashion-field)"
>
    <div class="fashion-section-inner fashion-split">
        <div>
            <p class="fashion-kicker">
                {{ __('capell-theme-minimal-fashion::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-minimal-fashion::sections.newsletter.heading')) }}
            </h2>
            <p class="fashion-lede">
                {{ data_get($section, 'summary', __('capell-theme-minimal-fashion::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="post"
            action="{{ data_get($section, 'action', '/') }}"
            class="fashion-card"
        >
            <label for="fashion-newsletter-email">
                {{ __('capell-theme-minimal-fashion::sections.newsletter.email_label') }}
            </label>
            <input
                id="fashion-newsletter-email"
                name="email"
                type="email"
                required
            />
            <button
                class="fashion-button"
                type="submit"
            >
                {{ __('capell-theme-minimal-fashion::sections.newsletter.button') }}
            </button>
        </form>
    </div>
</section>
