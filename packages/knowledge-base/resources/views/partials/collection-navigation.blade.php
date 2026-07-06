@php
    $headingLevel = max(2, min(6, (int) $headingLevel));
    $headingTag = 'h' . $headingLevel;
@endphp

<section>
    <{{ $headingTag }}>{{ $collection['title'] }}</{{ $headingTag }}>
    @if ($collection['description'] !== null)
        <p>{{ $collection['description'] }}</p>
    @endif

    @if ($collection['articles'] !== [])
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
    @endif

    @foreach ($collection['children'] as $childCollection)
        @include ('capell-knowledge-base::partials.collection-navigation', [
            'collection' => $childCollection,
            'headingLevel' => $headingLevel + 1,
        ])
    @endforeach
</section>
