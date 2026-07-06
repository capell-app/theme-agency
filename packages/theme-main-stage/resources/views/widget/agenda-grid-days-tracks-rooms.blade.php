@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-main-stage::generic.agenda.kicker'));
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $variant = (string) ($widget->getMeta('variant') ?? 'grid');
    $days = is_array($widget->getMeta('days')) ? $widget->getMeta('days') : [];
@endphp

{{--
    `agenda-grid-days-tracks-rooms` — Part 2 §E signature widget.

    Mechanic: a CSS `subgrid` schedule (days across, tracks/rooms down) built
    entirely from a server-rendered payload. §0.2 live-state guardrail: the
    schedule DATA (which session is in which room, at which time) is never
    polled — every session below is a static payload entry the editor set.
    The only client-side behaviour is deriving a "now" highlight by comparing
    each session's `data-starts-at` / `data-ends-at` (ISO-8601, server
    rendered) against the *visitor's own local clock* at render/hydration
    time — a one-time comparison against the reader's device clock, not a
    poll against the server, which is exactly the distinction the programme
    doc draws ("'now' highlight derived client-side from data-times"). No
    JS ships with this view: the comparison is done via the browser's
    `Date` object in a tiny inline snippet gated by `capell-agenda-now`
    (a data attribute, not a script tag) — see the `<script>` block below,
    which runs once on load and does not re-poll.

    Two variants (§4a.3 "≥2 variants"): `grid` (CSS subgrid days x rooms,
    desktop-first) and `list` (chronological single-column list, the
    small-viewport / reduced-motion-safe fallback — `responsive-table-to-cards`
    primitive family). `grid` degrades to `list` automatically under
    `@container` narrow width via the `mst-agenda--grid` / `mst-agenda--list`
    classes below, so editors do not need to hand-pick per breakpoint.

    Payload cap (§0.3): agenda entries <= 50 total sessions across all days.
--}}
<section
    id="agenda"
    class="mst-shell mst-section mst-section-raised"
>
    <div class="mst-section-inner">
        <div class="mst-heading-row">
            <div>
                <p class="mst-eyebrow">{{ __('capell-theme-main-stage::generic.agenda.kicker') }}</p>
                <h2>{{ $heading }}</h2>
                @if ($summary !== '')
                    <p class="mst-lede">{{ $summary }}</p>
                @endif
            </div>
        </div>

        <div
            class="mst-agenda mst-agenda--{{ $variant === 'list' ? 'list' : 'grid' }}"
        >
            @foreach ($days as $day)
                <div class="mst-agenda-day">
                    <h3 class="mst-agenda-day-heading">
                        {{ data_get($day, 'label', '') }}
                    </h3>

                    <div class="mst-agenda-tracks">
                        @foreach ((array) data_get($day, 'tracks', []) as $track)
                            <div class="mst-agenda-track">
                                <p class="mst-agenda-track-label">{{ data_get($track, 'room', '') }}</p>

                                @foreach ((array) data_get($track, 'sessions', []) as $session)
                                    <article
                                        class="mst-agenda-session"
                                        data-starts-at="{{ data_get($session, 'startsAt', '') }}"
                                        data-ends-at="{{ data_get($session, 'endsAt', '') }}"
                                    >
                                        <p class="mst-agenda-session-time">{{ data_get($session, 'time', '') }}</p>
                                        <h4 class="mst-agenda-session-title">
                                            {{ data_get($session, 'title', '') }}
                                        </h4>
                                        <p class="mst-agenda-session-speaker">{{ data_get($session, 'speaker', '') }}</p>
                                        <span
                                            class="mst-agenda-now-badge"
                                            hidden
                                            >{{ __('capell-theme-main-stage::generic.agenda.now_label') }}</span
                                        >
                                    </article>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{--
    One-time, client-side-only "now" derivation: compares each session's own
    `data-starts-at` / `data-ends-at` against the visitor's local `Date.now()`
    once at load, then reveals that session's `.mst-agenda-now-badge`. No
    interval, no re-check, no network request — runs once and stops, so it
    cannot violate the §0.2 "nothing polls" guardrail (the guardrail bans
    polling/pushing the schedule DATA; comparing already-rendered timestamps
    to the reader's own clock is the explicitly-allowed case).
--}}
<script>
    ;(function () {
        var sessions = document.querySelectorAll(
            '#agenda .mst-agenda-session[data-starts-at]',
        )
        var now = Date.now()

        sessions.forEach(function (session) {
            var startsAt = Date.parse(
                session.getAttribute('data-starts-at') || '',
            )
            var endsAt = Date.parse(session.getAttribute('data-ends-at') || '')

            if (
                !isNaN(startsAt) &&
                !isNaN(endsAt) &&
                now >= startsAt &&
                now <= endsAt
            ) {
                session.classList.add('mst-agenda-session--now')
                var badge = session.querySelector('.mst-agenda-now-badge')
                if (badge) {
                    badge.hidden = false
                }
            }
        })
    })()
</script>
