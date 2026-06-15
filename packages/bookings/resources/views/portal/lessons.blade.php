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
        <title>{{ __('capell-bookings::portal.lessons_title') }}</title>
    </head>
    <body>
        <main>
            <h1>{{ __('capell-bookings::portal.lessons_title') }}</h1>

            @foreach ($rows as $row)
                <article>
                    <h2>{{ $row->serviceName }}</h2>
                    <p>
                        {{ $row->startsAt->toDayDateTimeString() }} -
                        {{ $row->endsAt->format('H:i') }}
                    </p>
                    <p>{{ $row->status->getLabel() }}</p>

                    @foreach ($row->sharedNotes as $note)
                        <section>
                            <h3>{{ $note['summary'] }}</h3>
                            @if ($note['body'] !== null)
                                <p>{{ $note['body'] }}</p>
                            @endif
                        </section>
                    @endforeach
                </article>
            @endforeach
        </main>
    </body>
</html>
