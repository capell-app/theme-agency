<section
    id="subscribe"
    class="dnews-section dnews-section-tint"
>
    <div class="dnews-section-inner dnews-split">
        <div>
            <div class="dnews-section-head dnews-section-head-flush">
                <p class="dnews-kicker">
                    {{ __('capell-theme-ink-press::sections.newsletter.kicker') }}
                </p>
                <h2>
                    {{ data_get($section, 'heading', __('capell-theme-ink-press::sections.newsletter.heading')) }}
                </h2>
                <p class="dnews-lede">
                    {{ data_get($section, 'summary', __('capell-theme-ink-press::sections.newsletter.summary')) }}
                </p>
            </div>
        </div>

        {{--
            GET with a safe default action: Capell ships no newsletter endpoint,
            so a POST here would 419/405 on a real install.
        --}}
        <form
            method="get"
            action="{{ data_get($section, 'action', '#') }}"
            class="dnews-form"
        >
            <p class="dnews-meta">
                {{ __('capell-theme-ink-press::sections.newsletter.form_note') }}
            </p>
            <label for="dnews-newsletter-email">
                {{ __('capell-theme-ink-press::sections.newsletter.email_label') }}
            </label>
            <input
                id="dnews-newsletter-email"
                name="email"
                type="email"
                autocomplete="email"
                required
            />
            <button
                class="dnews-button"
                type="submit"
            >
                {{ __('capell-theme-ink-press::sections.newsletter.button') }}
            </button>
        </form>
    </div>
</section>
