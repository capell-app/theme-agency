@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-main-stage::generic.countdown.kicker'));
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $variant = (string) ($widget->getMeta('variant') ?? 'band');
    $deadline = (string) ($widget->getMeta('deadline') ?? '');
    $actionLabel = (string) ($widget->getMeta('actionLabel') ?? 'Get tickets');
    $actionUrl = (string) ($widget->getMeta('actionUrl') ?? '#tickets');
@endphp

{{--
    `countdown-band` — Part 2 §E signature widget, "countdown FOMO"
    differentiator. §0.2 live-state guardrail: the deadline is a single
    server-rendered `data-deadline` (ISO-8601) target set editorially by
    whoever configured the widget; the widget ticks client-side against the
    visitor's own clock only — it never polls or pushes a server value, and
    the countdown numerals themselves start from a static server-rendered
    value so the page is identical HTML for every visitor at cache time
    (html-cache-safe, §0.1) before the tiny inline tick script hydrates it.

    Two variants: `band` (full-width hero-adjacent strip, large numerals) and
    `compact` (an inline badge-style countdown for reuse inside a card or
    navigation area).
--}}
<section
    id="countdown"
    class="mst-shell mst-section mst-countdown mst-countdown--{{ $variant === 'compact' ? 'compact' : 'band' }}"
>
    <div class="mst-section-inner mst-countdown-inner">
        <div>
            <p class="mst-eyebrow">{{ __('capell-theme-main-stage::generic.countdown.kicker') }}</p>
            <h2>{{ $heading }}</h2>
            @if ($summary !== '')
                <p class="mst-lede">{{ $summary }}</p>
            @endif
        </div>

        <div
            class="mst-countdown-clock"
            data-deadline="{{ $deadline }}"
            aria-live="polite"
        >
            <div class="mst-countdown-unit">
                <span
                    class="mst-countdown-value"
                    data-unit="days"
                    >00</span
                >
                <span class="mst-countdown-label">Days</span>
            </div>
            <div class="mst-countdown-unit">
                <span
                    class="mst-countdown-value"
                    data-unit="hours"
                    >00</span
                >
                <span class="mst-countdown-label">Hours</span>
            </div>
            <div class="mst-countdown-unit">
                <span
                    class="mst-countdown-value"
                    data-unit="minutes"
                    >00</span
                >
                <span class="mst-countdown-label">Minutes</span>
            </div>
        </div>

        <a
            href="{{ $actionUrl }}"
            class="mst-button"
            >{{ $actionLabel }}</a
        >
    </div>
</section>

{{-- One-time tick loop against the visitor's own clock only — no server poll. --}}
<script>
    ;(function () {
        var clock = document.querySelector(
            '#countdown .mst-countdown-clock[data-deadline]',
        )
        if (!clock) {
            return
        }

        var deadline = Date.parse(clock.getAttribute('data-deadline') || '')
        if (isNaN(deadline)) {
            return
        }

        var daysEl = clock.querySelector('[data-unit="days"]')
        var hoursEl = clock.querySelector('[data-unit="hours"]')
        var minutesEl = clock.querySelector('[data-unit="minutes"]')

        function render() {
            var remainingMs = deadline - Date.now()
            if (remainingMs <= 0) {
                if (daysEl) daysEl.textContent = '00'
                if (hoursEl) hoursEl.textContent = '00'
                if (minutesEl) minutesEl.textContent = '00'
                return
            }

            var totalMinutes = Math.floor(remainingMs / 60000)
            var days = Math.floor(totalMinutes / (60 * 24))
            var hours = Math.floor((totalMinutes % (60 * 24)) / 60)
            var minutes = totalMinutes % 60

            if (daysEl) daysEl.textContent = String(days).padStart(2, '0')
            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0')
            if (minutesEl)
                minutesEl.textContent = String(minutes).padStart(2, '0')
        }

        render()
        setInterval(render, 60000)
    })()
</script>
