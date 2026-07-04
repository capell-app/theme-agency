@php
    $heading = data_get($section, 'heading', __('capell-theme-global-culture-magazine::sections.audio.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-global-culture-magazine::sections.audio.summary'));
    $episodes = data_get($section, 'items', [
        ['title' => __('capell-theme-global-culture-magazine::sections.audio.sequence_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.audio.sequence_summary')],
        ['title' => __('capell-theme-global-culture-magazine::sections.audio.caption_title'), 'summary' => __('capell-theme-global-culture-magazine::sections.audio.caption_summary')],
    ]);
@endphp

<section
    class="gcm-section gcm-section-dark"
    id="radio-audio"
>
    <div class="gcm-section-inner gcm-split">
        <div>
            <p class="gcm-kicker">
                {{ __('capell-theme-global-culture-magazine::sections.audio.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="gcm-lede">{{ $summary }}</p>
            <div class="gcm-actions">
                <a
                    class="gcm-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-global-culture-magazine::sections.audio.button')) }}
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
                        {{ data_get($episode, 'meta', __('capell-theme-global-culture-magazine::sections.audio.episode_meta')) }}
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
