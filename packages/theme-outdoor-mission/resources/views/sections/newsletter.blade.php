<section
    class="outdoor-section"
    style="background: var(--outdoor-field)"
>
    <div class="outdoor-section-inner outdoor-split">
        <div>
            <p class="outdoor-kicker">
                {{ __('capell-theme-outdoor-mission::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-outdoor-mission::sections.newsletter.heading')) }}
            </h2>
            <p class="outdoor-lede">
                {{ data_get($section, 'summary', __('capell-theme-outdoor-mission::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="post"
            action="{{ data_get($section, 'action', '/') }}"
            class="outdoor-card"
        >
            <label for="outdoor-newsletter-email">
                {{ __('capell-theme-outdoor-mission::sections.newsletter.email_label') }}
            </label>
            <input
                id="outdoor-newsletter-email"
                name="email"
                type="email"
                required
            />
            <button
                class="outdoor-button"
                type="submit"
            >
                {{ __('capell-theme-outdoor-mission::sections.newsletter.button') }}
            </button>
        </form>
    </div>
</section>
