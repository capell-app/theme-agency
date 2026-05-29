@props([
    'media' => null,
])

@if ($media?->enabled && ($media->hasImage() || $media->hasVideo()))
    @php
        $poster = $media->poster();
        $imageSources = [
            ['viewport' => 'mobile', 'query' => '(max-width: 639px)'],
            ['viewport' => 'tablet', 'query' => '(max-width: 1023px)'],
            ['viewport' => 'desktop', 'query' => '(min-width: 1024px)'],
        ];
        $videoSources = [
            ['viewport' => 'mobile', 'query' => '(max-width: 639px)'],
            ['viewport' => 'tablet', 'query' => '(max-width: 1023px)'],
            ['viewport' => 'desktop', 'query' => '(min-width: 1024px)'],
        ];
    @endphp

    <picture class="pointer-events-none absolute inset-0 block">
        @foreach ($imageSources as $source)
            @php($image = $media->images[$source['viewport']] ?? null)
            @if ($image)
                <source
                    media="{{ $source['query'] }}"
                    srcset="{{ $image->getUrl() }}"
                />
            @endif
        @endforeach

        @if ($poster)
            <img
                src="{{ $poster->getUrl() }}"
                alt=""
                class="h-full w-full object-cover object-center"
                loading="eager"
                decoding="async"
            />
        @endif
    </picture>

    @if ($media->hasVideo())
        <video
            class="pointer-events-none absolute inset-0 h-full w-full object-cover object-center"
            data-capell-hero-video
            @if ($media->pauseWhenOutOfView) data-pause-out-of-view="true" @endif
            @if ($media->autoplay) autoplay @endif
            @if ($media->loop) loop @endif
            @if ($media->muted) muted @endif
            playsinline
            preload="{{ $media->preload }}"
            @if ($poster) poster="{{ $poster->getUrl() }}" @endif
        >
            @foreach ($videoSources as $source)
                @php($video = $media->videos[$source['viewport']] ?? null)
                @if ($video)
                    <source
                        media="{{ $source['query'] }}"
                        src="{{ $video->getUrl() }}"
                        type="{{ $video->mime_type ?: 'video/mp4' }}"
                    />
                @endif
            @endforeach
        </video>
    @endif

    @once
        <script>
            ;(() => {
                const boot = () => {
                    const videos = Array.from(
                        document.querySelectorAll(
                            '[data-capell-hero-video][data-pause-out-of-view="true"]',
                        ),
                    )

                    if (!videos.length || !('IntersectionObserver' in window)) {
                        return
                    }

                    const reduceMotion =
                        window.matchMedia?.('(prefers-reduced-motion: reduce)')
                            .matches === true

                    videos.forEach((video) => {
                        if (video.dataset.capellHeroVideoReady === 'true') {
                            return
                        }

                        video.dataset.capellHeroVideoReady = 'true'

                        if (reduceMotion) {
                            video.pause()

                            return
                        }

                        new IntersectionObserver(
                            (entries) => {
                                entries.forEach((entry) => {
                                    if (entry.isIntersecting) {
                                        video.play().catch(() => {})

                                        return
                                    }

                                    video.pause()
                                })
                            },
                            { threshold: 0.18 },
                        ).observe(video)
                    })
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', boot, {
                        once: true,
                    })

                    return
                }

                boot()
            })()
        </script>
    @endonce
@endif
