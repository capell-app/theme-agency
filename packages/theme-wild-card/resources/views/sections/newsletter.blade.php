<section
    id="newsletter"
    class="exd-section exd-section-raised"
>
    <div class="exd-section-inner exd-split">
        <div>
            <p class="exd-kicker">
                {{ __('capell-theme-wild-card::sections.newsletter.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-wild-card::sections.newsletter.heading')) }}
            </h2>
            <p class="exd-lede">
                {{ data_get($section, 'summary', __('capell-theme-wild-card::sections.newsletter.summary')) }}
            </p>
        </div>
        <form
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
            class="exd-form"
        >
            <label for="exd-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-wild-card::sections.newsletter.email_label')) }}
            </label>
            <input
                id="exd-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                placeholder="{{ __('capell-theme-wild-card::sections.newsletter.placeholder') }}"
                required
            />
            <button
                class="exd-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-wild-card::sections.newsletter.button')) }}
            </button>
            <p class="exd-meta">
                {{ __('capell-theme-wild-card::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
