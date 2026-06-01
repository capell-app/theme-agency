<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>
            {{ __('capell-knowledge-base::generic.frontend.index_title') }}
        </title>
    </head>
    <body>
        <main>
            <h1>
                {{ __('capell-knowledge-base::generic.frontend.index_title') }}
            </h1>

            @forelse ($navigation as $collection)
                <section>
                    <h2>{{ $collection['title'] }}</h2>
                    @if ($collection['description'] !== null)
                        <p>{{ $collection['description'] }}</p>
                    @endif

                    <ul>
                        @foreach ($collection['articles'] as $article)
                            <li>
                                <a href="{{ $article['publicPath'] }}">
                                    {{ $article['title'] }}
                                </a>
                                @if ($article['summary'] !== null)
                                    <p>{{ $article['summary'] }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <p>
                    {{ __('capell-knowledge-base::generic.frontend.no_articles') }}
                </p>
            @endforelse
        </main>
    </body>
</html>
