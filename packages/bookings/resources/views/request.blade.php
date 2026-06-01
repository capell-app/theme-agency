<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="robots" content="noindex, nofollow" />
        <title>{{ __('capell-bookings::generic.frontend.title') }}</title>
        <style>
            :root {
                color-scheme: light dark;
                --booking-bg: #f6f8f7;
                --booking-panel: #ffffff;
                --booking-text: #17221d;
                --booking-muted: #65736c;
                --booking-border: #d8e1dc;
                --booking-accent: #1c6b4f;
                --booking-accent-text: #ffffff;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --booking-bg: #111714;
                    --booking-panel: #1a2420;
                    --booking-text: #f2f7f4;
                    --booking-muted: #a7b4ad;
                    --booking-border: #33443b;
                    --booking-accent: #93d8ba;
                    --booking-accent-text: #102018;
                }
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: var(--booking-bg);
                color: var(--booking-text);
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
                width: min(100%, 820px);
                margin: 0 auto;
                padding: 32px 20px 48px;
            }

            header,
            form,
            .status {
                margin-bottom: 24px;
                border: 1px solid var(--booking-border);
                border-radius: 8px;
                background: var(--booking-panel);
                padding: 24px;
            }

            h1,
            p {
                margin-top: 0;
            }

            .muted {
                color: var(--booking-muted);
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 16px;
            }

            label {
                display: grid;
                gap: 6px;
                font-weight: 650;
            }

            input,
            select,
            textarea {
                width: 100%;
                min-height: 42px;
                border: 1px solid var(--booking-border);
                border-radius: 6px;
                background: transparent;
                color: var(--booking-text);
                font: inherit;
                padding: 9px 11px;
            }

            textarea {
                min-height: 120px;
                resize: vertical;
            }

            button {
                min-height: 42px;
                border: 0;
                border-radius: 6px;
                background: var(--booking-accent);
                color: var(--booking-accent-text);
                cursor: pointer;
                font: inherit;
                font-weight: 700;
                padding: 0 16px;
            }

            .error {
                color: #b42318;
                font-size: 14px;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <main>
            <header>
                <h1>{{ __('capell-bookings::generic.frontend.title') }}</h1>
                <p class="muted">
                    {{ __('capell-bookings::generic.frontend.intro') }}
                </p>
            </header>

            @if (session('booking_request_status'))
                <div class="status" role="status">
                    {{ session('booking_request_status') }}
                </div>
            @endif

            @if ($options['services'] === [])
                <p class="status">
                    {{ __('capell-bookings::generic.frontend.no_services') }}
                </p>
            @else
                <form
                    method="post"
                    action="{{ route('capell-bookings.request.store') }}"
                >
                    @csrf
                    <div class="grid">
                        <label>
                            {{ __('capell-bookings::generic.frontend.service') }}
                            <select name="service_id" required>
                                @foreach ($options['services'] as $service)
                                    <option
                                        value="{{ $service['id'] }}"
                                        @selected((string) old('service_id') === (string) $service['id'])
                                    >
                                        {{ $service['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            {{ __('capell-bookings::generic.frontend.staff_member') }}
                            <select name="staff_member_id">
                                <option value="">
                                    {{ __('capell-bookings::generic.frontend.any_staff_member') }}
                                </option>
                                @foreach ($options['staff'] as $staffMember)
                                    <option
                                        value="{{ $staffMember['id'] }}"
                                        @selected((string) old('staff_member_id') === (string) $staffMember['id'])
                                    >
                                        {{ $staffMember['display_name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('staff_member_id')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            {{ __('capell-bookings::generic.frontend.location') }}
                            <select name="location_id">
                                <option value="">
                                    {{ __('capell-bookings::generic.frontend.any_location') }}
                                </option>
                                @foreach ($options['locations'] as $location)
                                    <option
                                        value="{{ $location['id'] }}"
                                        @selected((string) old('location_id') === (string) $location['id'])
                                    >
                                        {{ $location['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            {{ __('capell-bookings::generic.frontend.requested_starts_at') }}
                            <input
                                type="datetime-local"
                                name="requested_starts_at"
                                value="{{ old('requested_starts_at') }}"
                                required
                            />
                            @error('requested_starts_at')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            {{ __('capell-bookings::generic.frontend.timezone') }}
                            <input
                                type="text"
                                name="timezone"
                                value="{{ old('timezone', 'Europe/London') }}"
                                required
                            />
                            @error('timezone')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            {{ __('capell-bookings::generic.frontend.customer_name') }}
                            <input
                                type="text"
                                name="customer_name"
                                value="{{ old('customer_name') }}"
                                required
                                maxlength="255"
                            />
                            @error('customer_name')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            {{ __('capell-bookings::generic.frontend.customer_email') }}
                            <input
                                type="email"
                                name="customer_email"
                                value="{{ old('customer_email') }}"
                                required
                                maxlength="255"
                            />
                            @error('customer_email')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>

                        <label>
                            {{ __('capell-bookings::generic.frontend.customer_phone') }}
                            <input
                                type="text"
                                name="customer_phone"
                                value="{{ old('customer_phone') }}"
                                maxlength="255"
                            />
                            @error('customer_phone')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>

                    <p>
                        <label>
                            {{ __('capell-bookings::generic.frontend.notes') }}
                            <textarea name="notes" maxlength="2000">
{{ old('notes') }}</textarea
                            >
                            @error('notes')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </label>
                    </p>

                    <button type="submit">
                        {{ __('capell-bookings::generic.frontend.submit') }}
                    </button>
                </form>
            @endif
        </main>
    </body>
</html>
