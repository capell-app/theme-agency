<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        />
        <meta
            name="robots"
            content="noindex, nofollow"
        />
        <title>
            {{ __('capell-equestrian-clinics::package.coach.title') }}
        </title>
        <style>
            :root {
                color-scheme: light dark;
                --coach-bg: #f4f6f1;
                --coach-panel: #ffffff;
                --coach-text: #111812;
                --coach-muted: #536057;
                --coach-border: #cfd8cf;
                --coach-accent: #195d49;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --coach-bg: #0f1411;
                    --coach-panel: #19211c;
                    --coach-text: #f8faf7;
                    --coach-muted: #aeb8b1;
                    --coach-border: #39443d;
                    --coach-accent: #9de0c8;
                }
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: var(--coach-bg);
                color: var(--coach-text);
                font-family:
                    ui-sans-serif,
                    system-ui,
                    -apple-system,
                    BlinkMacSystemFont,
                    'Segoe UI',
                    sans-serif;
                line-height: 1.45;
            }

            main {
                width: min(100%, 920px);
                margin: 0 auto;
                padding: 18px 14px 40px;
            }

            header,
            section,
            .slot {
                border: 1px solid var(--coach-border);
                border-radius: 8px;
                background: var(--coach-panel);
                padding: 18px;
            }

            header,
            section,
            .slot {
                margin-bottom: 14px;
            }

            h1,
            h2,
            h3,
            p {
                margin-top: 0;
            }

            .muted {
                color: var(--coach-muted);
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
                gap: 12px;
            }

            .slot {
                display: grid;
                grid-template-columns: minmax(90px, 120px) 1fr;
                gap: 14px;
            }

            .time {
                color: var(--coach-accent);
                font-size: 18px;
                font-weight: 900;
            }

            .status {
                font-size: 15px;
                font-weight: 800;
            }

            @media (max-width: 620px) {
                .slot {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <main>
            <header>
                <h1>{{ $timetable['title'] }}</h1>
                <p class="muted">
                    {{ $timetable['date'] }} · {{ $timetable['window'] }}
                </p>
                <p>
                    <strong>{{ $timetable['venue']['name'] }}</strong>
                    <br />
                    {{ $timetable['venue']['address_line'] }}
                    {{ $timetable['venue']['postal_code'] }}
                </p>
            </header>

            <section>
                <h2>
                    {{ __('capell-equestrian-clinics::package.coach.minimum_viable_clinic') }}
                </h2>
                <div class="grid">
                    <p class="status">
                        {{ __('capell-equestrian-clinics::package.coach.attendees', ['current' => $timetable['minimum_viable_clinic']['current_attendees'], 'minimum' => $timetable['minimum_viable_clinic']['paid_attendees']]) }}
                    </p>
                    <p class="status">
                        {{ __('capell-equestrian-clinics::package.coach.revenue', ['current' => '£' . number_format($timetable['minimum_viable_clinic']['current_revenue_pence'] / 100, 2), 'minimum' => '£' . number_format($timetable['minimum_viable_clinic']['revenue_pence'] / 100, 2)]) }}
                    </p>
                </div>
            </section>

            @if ($timetable['venue']['facility_notes'] || $timetable['venue']['parking_notes'])
                <section>
                    @if ($timetable['venue']['facility_notes'])
                        <h2>
                            {{ __('capell-equestrian-clinics::package.coach.venue_notes') }}
                        </h2>
                        <p>{{ $timetable['venue']['facility_notes'] }}</p>
                    @endif

                    @if ($timetable['venue']['parking_notes'])
                        <h2>
                            {{ __('capell-equestrian-clinics::package.coach.parking_notes') }}
                        </h2>
                        <p>{{ $timetable['venue']['parking_notes'] }}</p>
                    @endif
                </section>
            @endif

            @foreach ($timetable['slots'] as $slot)
                <article class="slot">
                    <div class="time">{{ $slot['time'] }}</div>
                    <div>
                        <h2>{{ $slot['title'] }}</h2>
                        <p class="muted">
                            {{ $slot['archetype'] }}
                            @if ($slot['skill_tier'])
                                    · {{ $slot['skill_tier'] }}
                            @endif

                            · {{ $slot['booked_count'] }} /
                            {{ $slot['capacity_max'] }} booked
                            @if ($slot['waitlist_count'] > 0)
                                    · {{ $slot['waitlist_count'] }} waitlist
                            @endif
                        </p>
                        <p>
                            {{ __('capell-equestrian-clinics::package.coach.waiver_payment_status') }}
                        </p>
                        <p>
                            <strong>
                                {{ __('capell-equestrian-clinics::package.coach.resources') }}:
                            </strong>
                            @if ($slot['resources'] === [])
                                {{ __('capell-equestrian-clinics::package.coach.no_resources') }}
                            @else
                                {{ implode(', ', $slot['resources']) }}
                            @endif
                        </p>
                    </div>
                </article>
            @endforeach
        </main>
    </body>
</html>
