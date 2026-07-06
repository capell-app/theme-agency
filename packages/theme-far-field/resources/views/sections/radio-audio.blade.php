{{--
    Signature widget: audio-embedded-with-transcript (Part 2 §B, far-field).
    A native <audio> element paired with timestamped transcript blocks. A
    small inline script listens to the audio element's `timeupdate` event and
    toggles `data-active` on the transcript line whose `data-start`/`data-end`
    window contains the current playback position -- no external sync
    library, no polling, no client re-layout. All transcript timestamps and
    text come from the payload; when no audio URL is present the section
    still renders as a readable transcript with the episode index (§0.1: no
    widget may depend on a live media resource to be meaningful).
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-far-field::sections.audio.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-far-field::sections.audio.summary'));
    $audioUrl = (string) data_get($section, 'audio_url', '');
    $audioTitle = (string) data_get($section, 'audio_title', __('capell-theme-far-field::sections.audio.sequence_title'));
    $transcript = data_get($section, 'transcript', [
        ['start' => 0, 'end' => 18, 'speaker' => __('capell-theme-far-field::sections.audio.speaker_host'), 'text' => __('capell-theme-far-field::sections.audio.transcript_one')],
        ['start' => 18, 'end' => 42, 'speaker' => __('capell-theme-far-field::sections.audio.speaker_guest'), 'text' => __('capell-theme-far-field::sections.audio.transcript_two')],
        ['start' => 42, 'end' => 70, 'speaker' => __('capell-theme-far-field::sections.audio.speaker_host'), 'text' => __('capell-theme-far-field::sections.audio.transcript_three')],
    ]);
    $episodes = data_get($section, 'items', [
        ['title' => __('capell-theme-far-field::sections.audio.sequence_title'), 'summary' => __('capell-theme-far-field::sections.audio.sequence_summary')],
        ['title' => __('capell-theme-far-field::sections.audio.caption_title'), 'summary' => __('capell-theme-far-field::sections.audio.caption_summary')],
    ]);
    $variant = (string) data_get($section, 'variant', 'default');
@endphp

<section
    class="gcm-section gcm-section-dark"
    id="radio-audio"
    data-widget="audio-embedded-with-transcript"
    data-variant="{{ $variant }}"
>
    <div class="gcm-section-inner gcm-split">
        <div>
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.audio.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="gcm-lede">{{ $summary }}</p>

            @if ($audioUrl !== '')
                <div
                    class="gcm-audio-widget"
                    data-audio-transcript-widget
                >
                    <div class="gcm-audio-player">
                        <audio
                            controls
                            preload="none"
                            class="gcm-audio-element"
                            data-audio-transcript-player
                        >
                            <source
                                src="{{ $audioUrl }}"
                                type="audio/mpeg"
                            />
                        </audio>
                        <p class="gcm-meta gcm-audio-now-playing">
                            {{ $audioTitle }}
                        </p>
                    </div>

                    <ol
                        class="gcm-transcript"
                        data-transcript-for="{{ $audioTitle }}"
                    >
                        @foreach ($transcript as $line)
                            <li
                                class="gcm-transcript-line"
                                data-start="{{ (int) data_get($line, 'start', 0) }}"
                                data-end="{{ (int) data_get($line, 'end', 0) }}"
                            >
                                <span class="gcm-meta gcm-transcript-speaker">
                                    {{ data_get($line, 'speaker', '') }}
                                </span>
                                <p>{{ data_get($line, 'text', '') }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <script>
                    ;(function () {
                        var widget =
                            document.currentScript.previousElementSibling
                        if (
                            !widget ||
                            !widget.hasAttribute('data-audio-transcript-widget')
                        ) {
                            return
                        }
                        var player = widget.querySelector(
                            '[data-audio-transcript-player]',
                        )
                        var lines = Array.prototype.slice.call(
                            widget.querySelectorAll('.gcm-transcript-line'),
                        )
                        if (!player || lines.length === 0) {
                            return
                        }
                        player.addEventListener('timeupdate', function () {
                            var position = player.currentTime
                            lines.forEach(function (line) {
                                var start = Number(
                                    line.getAttribute('data-start'),
                                )
                                var end = Number(line.getAttribute('data-end'))
                                var isActive =
                                    position >= start && position < end
                                line.toggleAttribute('data-active', isActive)
                            })
                        })
                    })()
                </script>
            @endif

            <div class="gcm-actions">
                <a
                    class="gcm-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-far-field::sections.audio.button')) }}
                </a>
            </div>
        </div>

        <ol class="gcm-index">
            @foreach ($episodes as $episode)
                <li class="gcm-index-row">
                    <span
                        class="gcm-index-number"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <p class="gcm-meta">
                        {{ data_get($episode, 'meta', __('capell-theme-far-field::sections.audio.episode_meta')) }}
                    </p>
                    <div>
                        @php
                            $episodeTitle = (string) data_get($episode, 'title', data_get($episode, 'name', ''));
                            $episodeUrl = (string) data_get($episode, 'url', data_get($episode, 'href', ''));
                        @endphp

                        <h3>
                            @if ($episodeUrl !== '')
                                <a
                                    class="gcm-title-link"
                                    href="{{ $episodeUrl }}"
                                >
                                    {{ $episodeTitle }}
                                </a>
                            @else
                                {{ $episodeTitle }}
                            @endif
                        </h3>
                        <p>{{ data_get($episode, 'summary', '') }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
