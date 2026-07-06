@php
    $deadline = data_get($section, 'deadline');
    $isClosed = (bool) data_get($section, 'closed', false);
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.countdown.heading'));
@endphp

<section
    id="award-countdown-ticker"
    class="sbs-section sbs-countdown-compact"
>
    <div class="sbs-section-inner sbs-countdown-compact-inner">
        <span class="sbs-kicker">
            {{ $isClosed
                ? __('capell-theme-gold-rush::sections.countdown.state_closed')
                : __('capell-theme-gold-rush::sections.countdown.state_open') }}
        </span>
        <span>{{ $heading }}</span>

        @if (filled($deadline) && ! $isClosed)
            <span
                class="sbs-countdown sbs-countdown-inline"
                data-deadline="{{ $deadline }}"
            >
                <span
                    class="sbs-countdown-value"
                    data-deadline-value
                    aria-live="polite"
                >
                    &nbsp;
                </span>
            </span>
            @include ('capell-theme-gold-rush::partials.countdown-script')
        @else
            <span class="sbs-countdown-value">
                {{ __('capell-theme-gold-rush::sections.countdown.closed_value') }}
            </span>
        @endif
    </div>
</section>
