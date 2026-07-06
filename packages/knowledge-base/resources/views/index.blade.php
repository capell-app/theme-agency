<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    />
    <title>
        {{ __('capell-knowledge-base::generic.frontend.index_title') }}
    </title>
</head>
<body>
    <main>
        <h1>{{ __('capell-knowledge-base::generic.frontend.index_title') }}</h1>

        @forelse ($navigation as $collection)
            @include ('capell-knowledge-base::partials.collection-navigation', [
                    'collection' => $collection,
                    'headingLevel' => 2,
                ])
        @empty
            <p>
                {{ __('capell-knowledge-base::generic.frontend.no_articles') }}
            </p>
        @endforelse
    </main>
</body>
</html>
