{{--
    Variant: audio-embedded-with-transcript / immersive. Full-width plate
    treatment above the player, transcript set in a single wide column
    instead of the default split, for issues that want to lead with the
    audio rather than pair it beside the episode index.
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
@endphp

<section
    class="gcm-section gcm-section-dark"
    id="radio-audio"
    data-widget="audio-embedded-with-transcript"
    data-variant="immersive"
>
    <div class="gcm-section-inner gcm-centered">
        <p class="gcm-kicker">
            {{ __('capell-theme-far-field::sections.audio.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="gcm-lede">{{ $summary }}</p>

        @if ($audioUrl !== '')
            <div
                class="gcm-audio-widget gcm-audio-widget-immersive"
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
                    class="gcm-transcript gcm-transcript-wide"
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
                    var widget = document.currentScript.previousElementSibling
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
                            var start = Number(line.getAttribute('data-start'))
                            var end = Number(line.getAttribute('data-end'))
                            var isActive = position >= start && position < end
                            line.toggleAttribute('data-active', isActive)
                        })
                    })
                })()
            </script>
        @endif

        <div class="gcm-actions gcm-centered">
            <a
                class="gcm-button"
                href="{{ data_get($section, 'url', '/') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-far-field::sections.audio.button')) }}
            </a>
        </div>
    </div>
</section>
