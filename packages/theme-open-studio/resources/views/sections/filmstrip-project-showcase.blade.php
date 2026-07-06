{{--
    filmstrip-project-showcase — open-studio's headline "filmstrip scrubbing"
    mechanic (Part 2 §A): a scrub bar plus a thumbnail timeline let a visitor
    move frame by frame through a single project's build narrative, reading
    as sequential scrubbing rather than a generic carousel (§0.7 similarity
    policy). Frames are capped at twenty per §0.3; the native range input
    keeps the control keyboard-operable (arrow keys move one frame, Home/End
    jump to the first/last frame) with zero custom JS for that contract, and
    the inline script only mirrors the input's value onto the thumbnail rail
    and the current-frame caption — it never fetches or reorders anything.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-open-studio::sections.filmstrip.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-open-studio::sections.filmstrip.summary'));
    $frames = collect(data_get($section, 'frames', []))->take(20)->values();

    if ($frames->isEmpty()) {
        $frames = collect([
            ['title' => __('capell-theme-open-studio::sections.filmstrip.frame_title'), 'caption' => __('capell-theme-open-studio::sections.filmstrip.frame_caption')],
        ]);
    }

    $frameCount = $frames->count();
    $instanceId = 'filmstrip-' . data_get($section, 'instanceKey', 'default');
@endphp

<section
    id="filmstrip-project-showcase"
    class="csp-section"
>
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-open-studio::sections.filmstrip.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="csp-lede">{{ $summary }}</p>

        <div
            class="csp-filmstrip"
            data-filmstrip-project-showcase
            data-filmstrip-frame-count="{{ $frameCount }}"
        >
            <div class="csp-filmstrip-stage">
                @foreach ($frames as $index => $frame)
                    @php
                        $frameImage = data_get($frame, 'image', data_get($frame, 'imageUrl'));
                        $frameAlt = data_get($frame, 'imageAlt', data_get($frame, 'title', ''));
                    @endphp

                    <figure
                        class="csp-filmstrip-frame"
                        data-filmstrip-frame="{{ $index }}"
                        @if ($index > 0) hidden @endif
                    >
                        @if (filled($frameImage))
                            <img
                                src="{{ $frameImage }}"
                                alt="{{ $frameAlt }}"
                                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                decoding="async"
                                class="csp-cover csp-cover-wide"
                            />
                        @else
                            <div
                                class="csp-cover csp-cover-wide csp-cover-empty"
                                aria-hidden="true"
                            ></div>
                        @endif

                        <figcaption>
                            <p class="csp-filmstrip-frame-title">
                                {{ data_get($frame, 'title', '') }}
                            </p>
                            <p class="csp-filmstrip-frame-caption">
                                {{ data_get($frame, 'caption', data_get($frame, 'summary', '')) }}
                            </p>
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            <div class="csp-filmstrip-scrub-row">
                <label
                    class="csp-filmstrip-scrub-label"
                    for="{{ $instanceId }}-scrub"
                >
                    {{ __('capell-theme-open-studio::sections.filmstrip.scrub_label') }}
                </label>
                <input
                    id="{{ $instanceId }}-scrub"
                    type="range"
                    class="csp-filmstrip-scrub"
                    min="0"
                    max="{{ max($frameCount - 1, 0) }}"
                    value="0"
                    step="1"
                    data-filmstrip-scrub
                    aria-describedby="{{ $instanceId }}-position"
                />
                <span
                    id="{{ $instanceId }}-position"
                    class="csp-filmstrip-position"
                    data-filmstrip-position
                    aria-live="polite"
                >
                    {{ __('capell-theme-open-studio::sections.filmstrip.position', ['current' => 1, 'total' => $frameCount]) }}
                </span>
            </div>

            <ol
                class="csp-filmstrip-thumbs"
                data-filmstrip-thumbs
            >
                @foreach ($frames as $index => $frame)
                    @php
                        $thumbImage = data_get($frame, 'image', data_get($frame, 'imageUrl'));
                        $thumbAlt = data_get($frame, 'imageAlt', data_get($frame, 'title', ''));
                    @endphp

                    <li>
                        <button
                            type="button"
                            class="csp-filmstrip-thumb"
                            data-filmstrip-thumb="{{ $index }}"
                            aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-label="{{ __('capell-theme-open-studio::sections.filmstrip.thumb_label', ['position' => $index + 1, 'total' => $frameCount]) }}"
                        >
                            @if (filled($thumbImage))
                                <img
                                    src="{{ $thumbImage }}"
                                    alt=""
                                    loading="lazy"
                                    decoding="async"
                                    aria-hidden="true"
                                />
                            @else
                                <span
                                    class="csp-filmstrip-thumb-empty"
                                    aria-hidden="true"
                                ></span>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

<script>
    ;(function () {
        var root = document.currentScript.previousElementSibling
        var filmstrip =
            root && root.matches('[data-filmstrip-project-showcase]')
                ? root
                : null
        if (!filmstrip) {
            return
        }
        var scrub = filmstrip.querySelector('[data-filmstrip-scrub]')
        var frames = Array.prototype.slice.call(
            filmstrip.querySelectorAll('[data-filmstrip-frame]'),
        )
        var thumbs = Array.prototype.slice.call(
            filmstrip.querySelectorAll('[data-filmstrip-thumb]'),
        )
        var position = filmstrip.querySelector('[data-filmstrip-position]')
        if (!scrub || frames.length === 0) {
            return
        }

        function showFrame(index) {
            var total = frames.length
            var target = Math.max(0, Math.min(total - 1, index))

            frames.forEach(function (frame, frameIndex) {
                if (frameIndex === target) {
                    frame.removeAttribute('hidden')
                } else {
                    frame.setAttribute('hidden', '')
                }
            })

            thumbs.forEach(function (thumb, thumbIndex) {
                thumb.setAttribute(
                    'aria-current',
                    thumbIndex === target ? 'true' : 'false',
                )
            })

            if (position) {
                position.textContent = target + 1 + ' / ' + total
            }

            scrub.value = String(target)
        }

        scrub.addEventListener('input', function () {
            showFrame(parseInt(scrub.value, 10) || 0)
        })

        thumbs.forEach(function (thumb, thumbIndex) {
            thumb.addEventListener('click', function () {
                showFrame(thumbIndex)
            })
        })

        showFrame(0)
    })()
</script>
