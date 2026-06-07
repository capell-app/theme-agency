<div class="capell-page-events-listing">
    @foreach ($results ?? [] as $occurrence)
        <article>
            @if ($occurrence->url)
                <a href="{{ $occurrence->url }}">{{ $occurrence->title }}</a>
            @else
                <span>{{ $occurrence->title }}</span>
            @endif

            <time datetime="{{ $occurrence->isoStartsAt }}">
                {{ $occurrence->displayStartsAt }}
            </time>

            @if ($occurrence->venueName)
                <span>{{ $occurrence->venueName }}</span>
            @endif
        </article>
    @endforeach
</div>
