<section
    class="editorial-section"
    style="background: var(--editorial-field)"
>
    <div class="editorial-section-inner editorial-split">
        <div>
            <p class="editorial-kicker">
                {{ __('capell-theme-scoreboard-showcase::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-scoreboard-showcase::sections.newsletter.heading')) }}
            </h2>
            <p class="editorial-lede">
                {{ data_get($section, 'summary', __('capell-theme-scoreboard-showcase::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="post"
            action="{{ data_get($section, 'action', '/') }}"
            class="editorial-card"
        >
            <label for="editorial-newsletter-email">
                {{ __('capell-theme-scoreboard-showcase::sections.newsletter.email_label') }}
            </label>
            <input
                id="editorial-newsletter-email"
                name="email"
                type="email"
                required
            />
            <button
                class="editorial-button"
                type="submit"
            >
                {{ __('capell-theme-scoreboard-showcase::sections.newsletter.button') }}
            </button>
        </form>
    </div>
</section>
