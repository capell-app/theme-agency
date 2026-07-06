@php
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.featured.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.featured.summary'));
    $pick = data_get($section, 'pick', data_get($section, 'items.0', []));
    $upNext = collect(data_get($section, 'upNext', data_get($section, 'items', [])))->skip(1)->take(3);
    $deadline = data_get($section, 'nextRotationAt', data_get($section, 'deadline'));
    $pickImage = data_get($pick, 'image', data_get($pick, 'imageUrl'));
    $pickAlt = data_get($pick, 'imageAlt', data_get($pick, 'title', ''));
    $pickUrl = data_get($pick, 'url', data_get($pick, 'href'));
    $pickTitle = data_get($pick, 'title', data_get($pick, 'name', ''));
@endphp

<section
    id="featured-today-banner"
    class="exd-section exd-section-raised exd-featured-banner-split"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.featured.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        <div class="exd-featured-split-layout">
            <div class="exd-feature-slab exd-feature-slab-split">
                <div class="exd-feature-frame">
                    <span class="exd-feature-tag">
                        {{ __('capell-theme-wild-card::sections.featured.tag') }}
                    </span>

                    @if (filled($pickImage))
                        <img
                            src="{{ $pickImage }}"
                            alt="{{ $pickAlt }}"
                            width="1000"
                            height="800"
                            loading="lazy"
                            decoding="async"
                            class="exd-media"
                        />
                    @else
                        <div
                            class="exd-media exd-media-empty"
                            aria-hidden="true"
                        ></div>
                    @endif
                </div>
                <div>
                    <p class="exd-feature-meta">
                        {{ data_get($pick, 'meta', '') }}
                    </p>
                    <h3 class="exd-feature-title">
                        @if (filled($pickUrl))
                            <a
                                class="exd-title-link"
                                href="{{ $pickUrl }}"
                            >
                                {{ $pickTitle }}
                            </a>
                        @else
                            {{ $pickTitle }}
                        @endif
                    </h3>
                    <p>{{ data_get($pick, 'summary', '') }}</p>

                    @if (filled($deadline))
                        <p
                            class="exd-rotation-countdown"
                            data-deadline="{{ $deadline }}"
                            aria-live="polite"
                        >
                            <span class="exd-rotation-countdown-label">
                                {{ __('capell-theme-wild-card::sections.featured.rotation_label') }}
                            </span>
                            <span
                                class="exd-rotation-countdown-value"
                                data-deadline-value
                            >
                                &mdash;
                            </span>
                        </p>
                    @endif
                </div>
            </div>

            @if ($upNext->isNotEmpty())
                <ol class="exd-featured-up-next">
                    @foreach ($upNext as $nextItem)
                        <li class="exd-featured-up-next-item">
                            <span class="exd-meta">
                                {{ data_get($nextItem, 'meta', '') }}
                            </span>
                            <span>
                                {{ data_get($nextItem, 'title', data_get($nextItem, 'name', '')) }}
                            </span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

    @if (filled($deadline))
        <script>
            ;(function () {
                var section = document.currentScript.closest('section')
                if (!section) {
                    return
                }
                var banner = section.querySelector('[data-deadline]')
                var valueElement = section.querySelector(
                    '[data-deadline-value]',
                )
                if (!banner || !valueElement) {
                    return
                }
                var targetTime = Date.parse(
                    banner.getAttribute('data-deadline'),
                )
                if (isNaN(targetTime)) {
                    return
                }

                function tick() {
                    var remainingMs = targetTime - Date.now()
                    if (remainingMs <= 0) {
                        valueElement.textContent = '00:00:00'
                        clearInterval(intervalId)
                        return
                    }
                    var totalSeconds = Math.floor(remainingMs / 1000)
                    var hours = Math.floor(totalSeconds / 3600)
                    var minutes = Math.floor((totalSeconds % 3600) / 60)
                    var seconds = totalSeconds % 60
                    var pad = function (unit) {
                        return String(unit).padStart(2, '0')
                    }
                    valueElement.textContent =
                        pad(hours) + ':' + pad(minutes) + ':' + pad(seconds)
                }

                tick()
                var intervalId = setInterval(tick, 1000)
            })()
        </script>
    @endif
</section>
