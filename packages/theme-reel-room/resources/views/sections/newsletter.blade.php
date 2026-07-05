<section
    id="newsletter"
    class="mva-section mva-section-raised"
>
    <div class="mva-section-inner mva-newsletter-grid">
        <div>
            <p class="mva-kicker">
                {{ __('capell-theme-reel-room::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-reel-room::sections.newsletter.heading')) }}
            </h2>
            <p class="mva-lede">
                {{ data_get($section, 'summary', __('capell-theme-reel-room::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '/#newsletter') }}"
            class="mva-form"
        >
            <label for="mva-newsletter-email">
                {{ __('capell-theme-reel-room::sections.newsletter.email_label') }}
            </label>
            <input
                id="mva-newsletter-email"
                name="email"
                type="email"
                placeholder="{{ __('capell-theme-reel-room::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="mva-button"
                type="submit"
            >
                {{ __('capell-theme-reel-room::sections.newsletter.button') }}
            </button>
            <p class="mva-form-note">
                {{ __('capell-theme-reel-room::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
