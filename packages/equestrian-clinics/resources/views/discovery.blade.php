<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    />
    <title>{{ __('capell-equestrian-clinics::package.frontend.title') }}</title>
    <style>
        :root {
            color-scheme: light dark;
            --ec-bg: #f7f5f0;
            --ec-panel: #ffffff;
            --ec-text: #1f2520;
            --ec-muted: #657069;
            --ec-border: #d9ded6;
            --ec-accent: #2f6f5e;
            --ec-accent-text: #ffffff;
            --ec-warning: #8a5a00;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --ec-bg: #141815;
                --ec-panel: #202722;
                --ec-text: #f5f7f2;
                --ec-muted: #b7c0b9;
                --ec-border: #3d493f;
                --ec-accent: #a7dec9;
                --ec-accent-text: #132019;
                --ec-warning: #f6c96a;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--ec-bg);
            color: var(--ec-text);
            font-family:
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                'Segoe UI',
                sans-serif;
            line-height: 1.5;
        }

        main {
            width: min(100%, 1120px);
            margin: 0 auto;
            padding: 28px 18px 48px;
        }

        header,
        section,
        form,
        .clinic {
            border: 1px solid var(--ec-border);
            border-radius: 8px;
            background: var(--ec-panel);
            padding: 20px;
        }

        header,
        .search,
        .layout,
        .clinic,
        .host {
            margin-bottom: 18px;
        }

        h1,
        h2,
        h3,
        p {
            margin-top: 0;
        }

        .muted {
            color: var(--ec-muted);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
            gap: 18px;
        }

        label {
            display: grid;
            gap: 6px;
            font-weight: 700;
        }

        input,
        select,
        textarea {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--ec-border);
            border-radius: 6px;
            background: transparent;
            color: var(--ec-text);
            font: inherit;
            padding: 9px 11px;
        }

        textarea {
            min-height: 112px;
            resize: vertical;
        }

        button,
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            border: 0;
            border-radius: 6px;
            background: var(--ec-accent);
            color: var(--ec-accent-text);
            cursor: pointer;
            font: inherit;
            font-weight: 800;
            padding: 0 16px;
            text-decoration: none;
        }

        .slot {
            display: grid;
            grid-template-columns: minmax(92px, 120px) 1fr auto;
            gap: 12px;
            align-items: center;
            border-top: 1px solid var(--ec-border);
            padding: 12px 0;
        }

        .slot:first-child {
            border-top: 0;
        }

        .tag {
            display: inline-flex;
            width: fit-content;
            border: 1px solid var(--ec-border);
            border-radius: 999px;
            color: var(--ec-muted);
            font-size: 13px;
            font-weight: 700;
            padding: 2px 9px;
        }

        .status {
            border-color: color-mix(
                in srgb,
                var(--ec-accent) 55%,
                var(--ec-border)
            );
        }

        .warning {
            color: var(--ec-warning);
            font-weight: 800;
        }

        .error {
            color: #b42318;
            font-size: 14px;
            font-weight: 700;
        }

        @media (max-width: 760px) {
            .layout,
            .slot {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main>
        <header>
            <h1>
                {{ __('capell-equestrian-clinics::package.frontend.title') }}
            </h1>
            <p class="muted">
                {{ __('capell-equestrian-clinics::package.frontend.intro') }}
            </p>
        </header>

        <form
            class="search"
            method="get"
            action="{{ route('capell-equestrian-clinics.discovery') }}"
        >
            <div class="grid">
                <label>
                    {{ __('capell-equestrian-clinics::package.frontend.search') }}
                    <input
                        name="search"
                        value="{{ $filters['search'] }}"
                    />
                </label>
                <label>
                    {{ __('capell-equestrian-clinics::package.frontend.postcode') }}
                    <input
                        name="postcode"
                        value="{{ $filters['postcode'] }}"
                    />
                </label>
                <label>
                    {{ __('capell-equestrian-clinics::package.frontend.venue') }}
                    <select name="venue_id">
                        <option value="">
                            {{ __('capell-equestrian-clinics::package.frontend.all_venues') }}
                        </option>
                        @foreach ($venues as $venue)
                            <option
                                value="{{ $venue['id'] }}"
                                @selected ((string) $venue['id'] === $filters['venue_id'])
                            >
                                {{ $venue['name'] }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
            <p>
                <button type="submit">
                    {{ __('capell-equestrian-clinics::package.frontend.submit_search') }}
                </button>
            </p>
        </form>

        @if (session('equestrian_host_request_status'))
            <section
                class="status"
                role="status"
            >
                {{ session('equestrian_host_request_status') }}
            </section>
        @endif

        <div class="layout">
            <div>
                @forelse ($tour_days as $tourDay)
                    <article class="clinic">
                        <h2>{{ $tourDay['title'] }}</h2>
                        <p class="muted">
                            {{ $tourDay['date'] }} · {{ $tourDay['starts_at'] }} - {{ $tourDay['ends_at'] }} · {{ $tourDay['venue']['name'] }}
                            @if ($tourDay['distance_miles'] !== null)
                                · {{ $tourDay['distance_miles'] }}
                                miles
                            @endif
                        </p>
                        <p>
                            {{ $tourDay['venue']['address_line'] }}
                            @if ($tourDay['venue']['postal_code'])
                                {{ $tourDay['venue']['postal_code'] }}
                            @endif
                        </p>
                        @if ($tourDay['venue']['facility_notes'])
                            <p class="muted">
                                {{ $tourDay['venue']['facility_notes'] }}
                            </p>
                        @endif

                        <p class="tag">
                            {{ __('capell-equestrian-clinics::package.frontend.spots_remaining', ['count' => $tourDay['spots_remaining']]) }}
                        </p>

                        @foreach ($tourDay['slots'] as $slot)
                            <div class="slot">
                                <strong>
                                    {{ $slot['starts_at'] }} - {{ $slot['ends_at'] }}
                                </strong>
                                <div>
                                    <h3>{{ $slot['title'] }}</h3>
                                    <p class="muted">
                                        {{ $slot['archetype'] }}
                                        @if ($slot['skill_tier'])
                                            · {{ $slot['skill_tier'] }}
                                        @endif

                                        · {{ __('capell-equestrian-clinics::package.frontend.spots_remaining', ['count' => $slot['remaining_capacity']]) }}
                                    </p>
                                </div>

                                @if ($slot['is_full'])
                                    <span class="warning">
                                        {{ __('capell-equestrian-clinics::package.frontend.join_waitlist') }}
                                    </span>
                                @else
                                    <span class="button">
                                        {{ __('capell-equestrian-clinics::package.frontend.request_slot') }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </article>
                @empty
                    <section>
                        {{ __('capell-equestrian-clinics::package.frontend.no_clinics') }}
                    </section>
                @endforelse
            </div>

            <aside>
                <section class="host">
                    <h2>
                        {{ __('capell-equestrian-clinics::package.frontend.host_title') }}
                    </h2>
                    <p class="muted">
                        {{ __('capell-equestrian-clinics::package.frontend.host_intro') }}
                    </p>
                    <form
                        method="post"
                        action="{{ $postUrl }}"
                    >
                        @csrf
                        <label>
                            {{ __('capell-equestrian-clinics::package.frontend.requester_name') }}
                            <input
                                name="requester_name"
                                value="{{ old('requester_name') }}"
                                required
                            />
                            @error ('requester_name')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>
                        <label>
                            {{ __('capell-equestrian-clinics::package.frontend.requester_email') }}
                            <input
                                type="email"
                                name="requester_email"
                                value="{{ old('requester_email') }}"
                                required
                            />
                            @error ('requester_email')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>
                        <label>
                            {{ __('capell-equestrian-clinics::package.frontend.venue_name') }}
                            <input
                                name="venue_name"
                                value="{{ old('venue_name') }}"
                            />
                        </label>
                        <label>
                            {{ __('capell-equestrian-clinics::package.frontend.preferred_region') }}
                            <input
                                name="preferred_region"
                                value="{{ old('preferred_region') }}"
                            />
                        </label>
                        <label>
                            {{ __('capell-equestrian-clinics::package.frontend.expected_riders') }}
                            <input
                                type="number"
                                min="1"
                                max="200"
                                name="expected_riders"
                                value="{{ old('expected_riders') }}"
                            />
                        </label>
                        <label>
                            {{ __('capell-equestrian-clinics::package.frontend.message') }}
                            <textarea
                                name="message"
                                >{{ old('message') }}</textarea
                            >
                        </label>
                        <p>
                            <button type="submit">
                                {{ __('capell-equestrian-clinics::package.frontend.send_request') }}
                            </button>
                        </p>
                    </form>
                </section>

                <section>
                    <h2>
                        {{ __('capell-equestrian-clinics::package.frontend.demand_title') }}
                    </h2>
                    @foreach ($heatmap as $row)
                        <p>
                            <strong>{{ $row['region'] }}</strong>
                            <br />
                            <span class="muted">
                                {{ $row['requests'] }} requests · {{ $row['expected_riders'] }} riders
                            </span>
                        </p>
                    @endforeach
                </section>
            </aside>
        </div>
    </main>
</body>
</html>
