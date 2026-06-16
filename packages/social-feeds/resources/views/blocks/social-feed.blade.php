@php
    use Capell\SocialFeeds\Data\SocialFeedRenderData;

    /** @var SocialFeedRenderData $feed */
    $layout = $feed->config->layout->value;
    $columns = $feed->config->columns;
    $pageSize = $feed->config->pageSize;
@endphp

<section
    class="capell-social-feed capell-social-feed--{{ $layout }}"
    data-layout="{{ $layout }}"
    data-page-size="{{ $pageSize }}"
    data-autoplay="{{ $feed->config->autoplay ? 'true' : 'false' }}"
    data-transition-ms="{{ $feed->config->transitionMs }}"
    style="--capell-social-feed-columns: {{ $columns }}"
>
    @if ($feed->items === [])
        <p class="capell-social-feed__empty">
            {{ __('capell-social-feeds::package.empty') }}
        </p>
    @else
        <div class="capell-social-feed__items">
            @foreach ($feed->items as $item)
                <article class="capell-social-feed__item">
                    @if ($feed->config->showMedia && $item->mediaUrl !== null)
                        <a
                            class="capell-social-feed__media capell-social-feed__media--{{ $feed->config->aspectRatio }}"
                            href="{{ $item->permalink ?? $item->mediaUrl }}"
                            rel="noopener noreferrer"
                            target="_blank"
                        >
                            <img
                                src="{{ $item->thumbnailUrl ?? $item->mediaUrl }}"
                                alt="{{ $feed->config->mediaAltText($item) }}"
                                loading="lazy"
                            />
                        </a>
                    @endif

                    <div class="capell-social-feed__body">
                        @if ($feed->config->showAuthor && $item->authorName !== null)
                            <p class="capell-social-feed__author">
                                {{ $item->authorName }}
                            </p>
                        @endif

                        @if ($feed->config->showCaption && $item->text !== null)
                            <p class="capell-social-feed__caption">
                                {{ $item->text }}
                            </p>
                        @endif

                        @if ($feed->config->showDate && $item->publishedAt !== null)
                            <time
                                class="capell-social-feed__date"
                                datetime="{{ $item->publishedAt->toIso8601String() }}"
                            >
                                {{ $item->publishedAt->toFormattedDateString() }}
                            </time>
                        @endif

                        @if ($item->permalink !== null)
                            <a
                                class="capell-social-feed__link"
                                href="{{ $item->permalink }}"
                                rel="noopener noreferrer"
                                target="_blank"
                            >
                                {{ parse_url($item->permalink, PHP_URL_HOST) ?: $item->provider }}
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
