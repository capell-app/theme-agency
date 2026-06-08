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
                {{ $occurrence->eventTimezone }}
            </time>

            @if ($occurrence->viewerDisplayStartsAt && $occurrence->viewerTimezone !== $occurrence->eventTimezone)
                <time datetime="{{ $occurrence->isoStartsAt }}">
                    {{ __('capell-events::generic.your_time') }}:
                    {{ $occurrence->viewerDisplayStartsAt }}
                    {{ $occurrence->viewerTimezone }}
                </time>
            @endif

            @if ($occurrence->venueName)
                <span>{{ $occurrence->venueName }}</span>
            @endif
        </article>
    @endforeach
</div>
