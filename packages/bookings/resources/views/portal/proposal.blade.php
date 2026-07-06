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
    <title>{{ __('capell-bookings::portal.proposal_title') }}</title>
</head>
<body>
    <main>
        <h1>{{ __('capell-bookings::portal.proposal_title') }}</h1>

        @if (session('booking_portal_status'))
            <p>{{ session('booking_portal_status') }}</p>
        @endif

        <p>
            {{ $proposalParty->proposal?->appointmentRequest?->customer_name }}
        </p>
        <p>
            {{ $proposalParty->proposal?->proposed_starts_at?->toDayDateTimeString() }}
        </p>

        <form
            method="post"
            action="{{ URL::temporarySignedRoute('capell-bookings.portal.proposal.respond', $signatureExpiresAt, ['token' => $token]) }}"
        >
            @csrf
            <button
                type="submit"
                name="accepted"
                value="1"
            >
                {{ __('capell-bookings::portal.accept_proposal') }}
            </button>
            <button
                type="submit"
                name="accepted"
                value="0"
            >
                {{ __('capell-bookings::portal.reject_proposal') }}
            </button>
        </form>
    </main>
</body>
</html>
