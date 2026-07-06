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
    <title>{{ __('capell-bookings::portal.review_title') }}</title>
</head>
<body>
    <main>
        <h1>{{ __('capell-bookings::portal.review_title') }}</h1>

        @if (session('booking_portal_status'))
            <p>{{ session('booking_portal_status') }}</p>
        @endif

        @if ($reviewParticipant->completed_at !== null)
            <p>{{ __('capell-bookings::portal.review_already_saved') }}</p>
        @else
            <p>
                {{ __('capell-bookings::portal.review_participant_intro', ['role' => $reviewParticipant->role]) }}
            </p>

            <form
                method="post"
                action="{{ URL::temporarySignedRoute('capell-bookings.portal.review-participant.store', $signatureExpiresAt, ['token' => $token]) }}"
            >
                @csrf
                <label>
                    {{ __('capell-bookings::portal.rating') }}
                    <input
                        type="number"
                        name="rating"
                        min="1"
                        max="5"
                        required
                    />
                </label>
                <label>
                    {{ __('capell-bookings::portal.response') }}
                    <textarea
                        name="response"
                        maxlength="2000"
                    ></textarea>
                </label>
                <button type="submit">
                    {{ __('capell-bookings::portal.submit_review') }}
                </button>
            </form>
        @endif
    </main>
</body>
</html>
