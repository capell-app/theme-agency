@props([
    'media' => null,
])

@if ($media?->enabled && ($media->hasImage() || $media->hasVideo()))
    @php
        $poster = $media->poster();
        $posterSrcset = $media->posterSrcset();
    @endphp

    <picture class="widget pointer-events-none absolute inset-0">
        @foreach ($media->imageSources() as $source)
            <source
                media="{{ $source['media'] }}"
                srcset="{{ $source['srcset'] }}"
                sizes="{{ $source['sizes'] }}"
            />
        @endforeach

        @if ($poster)
            <img
                src="{{ $poster->getUrl() }}"
                @if ($posterSrcset) srcset="{{ $posterSrcset }}" @endif
                sizes="{{ $media::FullBleedImageSizes }}"
                alt=""
                class="h-full w-full object-cover object-center"
                loading="eager"
                fetchpriority="high"
                decoding="async"
            />
        @endif
    </picture>

    @if ($media->hasVideo())
        <video
            class="pointer-events-none absolute inset-0 h-full w-full object-cover object-center"
            data-hero-video
            @if ($media->pauseWhenOutOfView) data-pause-out-of-view="true" @endif
            @if ($media->autoplay) autoplay @endif
            @if ($media->loop) loop @endif
            @if ($media->muted) muted @endif
            playsinline
            preload="{{ $media->preload }}"
            @if ($poster) poster="{{ $poster->getUrl() }}" @endif
        >
            @foreach ($media->videoSources() as $source)
                <source
                    media="{{ $source['media'] }}"
                    src="{{ $source['src'] }}"
                    type="{{ $source['type'] }}"
                />
            @endforeach
        </video>
    @endif

    @once
        <script>
            ;(() => {
                const boot = () => {
                    const videos = Array.from(
                        document.querySelectorAll(
                            '[data-hero-video][data-pause-out-of-view="true"]',
                        ),
                    )

                    if (!videos.length || !('IntersectionObserver' in window)) {
                        return
                    }

                    const reduceMotion =
                        window.matchMedia?.('(prefers-reduced-motion: reduce)')
                            .matches === true

                    videos.forEach((video) => {
                        if (video.dataset.heroVideoReady === 'true') {
                            return
                        }

                        video.dataset.heroVideoReady = 'true'

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
