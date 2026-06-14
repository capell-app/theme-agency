@props([
    'alt' => '',
    'media' => null,
])

@if ($media?->enabled && ($media->hasImage() || $media->hasVideo()))
    @php
        $poster = $media->poster();
        $posterAlt = $media->hasVideo() ? '' : $alt;
        $posterSrcset = $media->posterSrcset();
        $toggleLabel = $media->autoplay
            ? __('capell-hero::frontend.pause_video')
            : __('capell-hero::frontend.play_video');
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
                alt="{{ $posterAlt }}"
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

        <button
            type="button"
            class="pointer-events-auto absolute right-4 bottom-4 z-20 inline-flex h-10 w-10 items-center justify-center rounded-full bg-black/55 text-white shadow-sm transition hover:bg-black/70 focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-black/30 focus:outline-none"
            data-hero-video-toggle
            data-label-pause="{{ __('capell-hero::frontend.pause_video') }}"
            data-label-play="{{ __('capell-hero::frontend.play_video') }}"
            aria-label="{{ $toggleLabel }}"
            aria-pressed="{{ $media->autoplay ? 'true' : 'false' }}"
            title="{{ $toggleLabel }}"
        >
            <span
                data-hero-video-pause-icon
                @class(['hidden' => ! $media->autoplay])
            >
                @svg('heroicon-o-pause', 'h-5 w-5')
            </span>
            <span
                data-hero-video-play-icon
                @class(['hidden' => $media->autoplay])
            >
                @svg('heroicon-o-play', 'h-5 w-5')
            </span>
        </button>
    @endif

    @once
        <script>
            ;(() => {
                const boot = () => {
                    const videos = Array.from(
                        document.querySelectorAll('[data-hero-video]'),
                    )

                    if (!videos.length) {
                        return
                    }

                    const reduceMotion =
                        window.matchMedia?.('(prefers-reduced-motion: reduce)')
                            .matches === true

                    const syncButton = (video) => {
                        const host = video.closest('.swiper-slide-inner')
                        const button = host?.querySelector(
                            '[data-hero-video-toggle]',
                        )

                        if (!button) {
                            return
                        }

                        const isPlaying = !video.paused && !video.ended
                        const playIcon = button.querySelector(
                            '[data-hero-video-play-icon]',
                        )
                        const pauseIcon = button.querySelector(
                            '[data-hero-video-pause-icon]',
                        )

                        button.setAttribute(
                            'aria-label',
                            isPlaying
                                ? button.dataset.labelPause
                                : button.dataset.labelPlay,
                        )
                        button.setAttribute(
                            'title',
                            isPlaying
                                ? button.dataset.labelPause
                                : button.dataset.labelPlay,
                        )
                        button.setAttribute(
                            'aria-pressed',
                            isPlaying ? 'true' : 'false',
                        )
                        playIcon?.classList.toggle('hidden', isPlaying)
                        pauseIcon?.classList.toggle('hidden', !isPlaying)
                    }

                    const bindToggle = (video) => {
                        const host = video.closest('.swiper-slide-inner')
                        const button = host?.querySelector(
                            '[data-hero-video-toggle]',
                        )

                        if (
                            !button ||
                            button.dataset.heroVideoToggleReady === 'true'
                        ) {
                            return
                        }

                        button.dataset.heroVideoToggleReady = 'true'
                        button.addEventListener('click', () => {
                            if (video.paused || video.ended) {
                                video.dataset.heroVideoUserPaused = 'false'
                                const play = video.play()

                                if (play?.then) {
                                    play.then(() => syncButton(video)).catch(
                                        () => syncButton(video),
                                    )

                                    return
                                }

                                syncButton(video)

                                return
                            }

                            video.dataset.heroVideoUserPaused = 'true'
                            video.pause()
                            syncButton(video)
                        })

                        video.addEventListener('play', () => syncButton(video))
                        video.addEventListener('pause', () => syncButton(video))
                        syncButton(video)
                    }

                    videos.forEach((video) => {
                        if (video.dataset.heroVideoReady === 'true') {
                            return
                        }

                        video.dataset.heroVideoReady = 'true'
                        bindToggle(video)

                        if (reduceMotion) {
                            video.pause()
                            syncButton(video)

                            return
                        }

                        if (
                            video.dataset.pauseOutOfView !== 'true' ||
                            !('IntersectionObserver' in window)
                        ) {
                            return
                        }

                        new IntersectionObserver(
                            (entries) => {
                                entries.forEach((entry) => {
                                    if (entry.isIntersecting) {
                                        if (
                                            video.dataset
                                                .heroVideoUserPaused === 'true'
                                        ) {
                                            return
                                        }

                                        const play = video.play()
                                        play?.catch(() => {})

                                        return
                                    }

                                    video.pause()
                                    syncButton(video)
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
