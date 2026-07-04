@php
    $heading = data_get($section, 'heading', __('capell-theme-scoreboard-showcase::sections.winner.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-scoreboard-showcase::sections.winner.summary'));
    $title = data_get($section, 'title', data_get($section, 'name'));
    $url = data_get($section, 'url', data_get($section, 'href'));
    $image = data_get($section, 'image', data_get($section, 'imageUrl'));
    $imageAlt = data_get($section, 'imageAlt', $title ?? $heading);
    $scores = collect(data_get($section, 'items', data_get($section, 'scores', [])))
        ->filter(fn (mixed $score): bool => filled(data_get($score, 'title', data_get($score, 'name'))))
        ->values();
@endphp

<section
    id="winner-hero"
    class="sbs-section"
>
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-scoreboard-showcase::sections.winner.rank_label') }}
            1 —
            {{ __('capell-theme-scoreboard-showcase::sections.winner.heading') }}
        </p>

        <div class="sbs-podium">
            <article class="sbs-podium-lead">
                @if (filled($image))
                    <div class="sbs-podium-media">
                        <img
                            src="{{ $image }}"
                            alt="{{ $imageAlt }}"
                            loading="lazy"
                            decoding="async"
                            class="sbs-podium-media-image"
                        />
                        <span class="sbs-podium-rank">1</span>
                    </div>
                @else
                    <div
                        class="sbs-podium-media"
                        aria-hidden="true"
                    >
                        <span class="sbs-podium-rank">1</span>
                    </div>
                @endif

                <div class="sbs-podium-body">
                    <h2>
                        @if (filled($url))
                            <a
                                class="sbs-title-link"
                                href="{{ $url }}"
                            >
                                {{ $title ?? $heading }}
                            </a>
                        @else
                            {{ $title ?? $heading }}
                        @endif
                    </h2>
                    <p class="sbs-podium-note">{{ $summary }}</p>

                    @if ($scores->isNotEmpty())
                        <div class="sbs-podium-score-list">
                            @foreach ($scores as $score)
                                @php
                                    $scoreTitle = data_get($score, 'title', data_get($score, 'name', ''));
                                    [$scoreName, $scoreValue] = str_contains((string) $scoreTitle, '—')
                                        ? array_map('trim', explode('—', (string) $scoreTitle, 2))
                                        : [$scoreTitle, __('capell-theme-scoreboard-showcase::sections.winner.score_default_value')];
                                @endphp

                                <div class="sbs-podium-score-row">
                                    <span class="sbs-score-name">
                                        {{ $scoreName }}
                                    </span>
                                    <span class="sbs-score-value">
                                        {{ $scoreValue }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </article>

            @if (filled($url))
                <a
                    class="sbs-link-arrow"
                    href="{{ $url }}"
                >
                    {{ __('capell-theme-scoreboard-showcase::sections.winner.read_more') }}
                </a>
            @endif
        </div>
    </div>
</section>
