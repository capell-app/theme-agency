@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.newsletter.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-field-guide::sections.newsletter.summary'));
@endphp

<section
    id="newsletter"
    class="fga-section fga-section-panel"
>
    <div class="fga-section-inner fga-newsletter-grid">
        <div class="fga-section-head-copy">
            <p class="fga-kicker">
                {{ __('capell-theme-field-guide::sections.newsletter.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="fga-lede">{{ $summary }}</p>
        </div>

        <form
            class="fga-form"
            method="get"
            action="{{ data_get($section, 'action', '#newsletter') }}"
        >
            <label for="fga-newsletter-email">
                {{ data_get($section, 'email_label', __('capell-theme-field-guide::sections.newsletter.email_label')) }}
            </label>
            <input
                id="fga-newsletter-email"
                name="email"
                type="email"
                placeholder="{{ __('capell-theme-field-guide::sections.newsletter.email_placeholder') }}"
                required
            />
            <button
                class="fga-button"
                type="submit"
            >
                {{ data_get($section, 'button', __('capell-theme-field-guide::sections.newsletter.button')) }}
            </button>
            <p class="fga-mono-note">
                {{ __('capell-theme-field-guide::sections.newsletter.note') }}
            </p>
        </form>
    </div>
</section>
