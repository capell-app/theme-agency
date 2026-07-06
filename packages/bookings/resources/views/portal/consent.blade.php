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
        content="noindex,nofollow"
    />
    <title>{{ __('capell-bookings::portal.consent_title') }}</title>
</head>
<body>
    <main>
        <h1>{{ __('capell-bookings::portal.consent_title') }}</h1>

        @if (session('booking_portal_status'))
            <p>{{ session('booking_portal_status') }}</p>
        @endif

        @foreach ($channels as $channel)
            @php ($consent = $consents->get($channel->value))
            <form
                method="post"
                action="{{ URL::temporarySignedRoute('capell-bookings.portal.consent.update', $signatureExpiresAt, ['portalToken' => $portalToken]) }}"
            >
                @csrf
                <input
                    type="hidden"
                    name="channel"
                    value="{{ $channel->value }}"
                />
                <label>
                    {{ $channel->getLabel() }}
                    <input
                        type="text"
                        name="recipient"
                        value="{{ old('recipient', $consent?->recipient ?? $portalAccount->email) }}"
                    />
                </label>
                <button
                    type="submit"
                    name="granted"
                    value="1"
                >
                    {{ __('capell-bookings::portal.grant_consent') }}
                </button>
                <button
                    type="submit"
                    name="granted"
                    value="0"
                >
                    {{ __('capell-bookings::portal.revoke_consent') }}
                </button>
            </form>
        @endforeach
    </main>
</body>
</html>
