@php
    // "Open / closed" is an editorial payload state computed from the deadline
    // at render time — never detected client-side (Wave 2 §0.2).
    $deadline = data_get($section, 'deadline');
    $isClosed = (bool) data_get($section, 'closed', false);
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.countdown.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-gold-rush::sections.countdown.summary'));
@endphp

<section
    id="award-countdown-ticker"
    class="sbs-section sbs-section-dark"
>
    <div class="sbs-section-inner sbs-countdown-ticker-inner">
        <div>
            <p class="sbs-kicker">
                {{ $isClosed
                    ? __('capell-theme-gold-rush::sections.countdown.state_closed')
                    : __('capell-theme-gold-rush::sections.countdown.state_open') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="sbs-lede">{{ $summary }}</p>
        </div>

        @if (filled($deadline) && ! $isClosed)
            <div
                class="sbs-countdown sbs-countdown-ticker"
                data-deadline="{{ $deadline }}"
            >
                <span
                    class="sbs-countdown-value sbs-countdown-value-large"
                    data-deadline-value
                    aria-live="polite"
                >
                    &nbsp;
                </span>
                <span class="sbs-countdown-label">
                    {{ __('capell-theme-gold-rush::sections.countdown.until_close') }}
                </span>
            </div>
            @include ('capell-theme-gold-rush::partials.countdown-script')
        @else
            <div class="sbs-countdown-ticker sbs-countdown-ticker-closed">
                <span class="sbs-countdown-value sbs-countdown-value-large">
                    {{ __('capell-theme-gold-rush::sections.countdown.closed_value') }}
                </span>
            </div>
        @endif
    </div>
</section>
