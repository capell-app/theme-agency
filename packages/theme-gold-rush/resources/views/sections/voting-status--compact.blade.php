@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.voting.heading'));
    $items = collect(data_get($section, 'items', []))->values();
    $deadline = data_get($section, 'deadline');
    $deadlineLabel = data_get($section, 'deadline_label', __('capell-theme-gold-rush::sections.voting.deadline_label'));
    $isClosed = (bool) data_get($section, 'closed', false);

    $tallies = $items->map(function (mixed $item): int {
        $meta = (string) data_get($item, 'meta', '');

        return preg_match('/\d[\d,]*/', $meta, $matches) === 1
            ? (int) str_replace(',', '', $matches[0])
            : 0;
    });

    $totalVotes = (int) $tallies->sum();
    $leadTally = (int) ($tallies->first() ?? 0);
    $leaderPercentage = $totalVotes > 0 ? round(($leadTally / $totalVotes) * 100, 1) : 0.0;
    $gaugeGradient = sprintf(
        'var(--sbs-accent) 0deg %.1fdeg, var(--sbs-line) %.1fdeg 360deg',
        $leaderPercentage * 3.6,
        $leaderPercentage * 3.6,
    );
@endphp

<section
    id="voting-status"
    class="sbs-section sbs-vote-compact"
>
    <div class="sbs-section-inner sbs-vote-compact-inner">
        <div
            class="sbs-vote-gauge sbs-vote-gauge-small"
            style="--sbs-gauge-gradient: {{ $gaugeGradient }}"
            role="img"
            aria-label="{{ __('capell-theme-gold-rush::sections.voting.gauge_label', ['percentage' => $leaderPercentage]) }}"
        >
            <span class="sbs-vote-gauge-value">
                {{ rtrim(rtrim(number_format($leaderPercentage, 1), '0'), '.') }}%
            </span>
        </div>

        <div>
            <p class="sbs-kicker">
                {{ $isClosed
                    ? __('capell-theme-gold-rush::sections.voting.status_closed')
                    : __('capell-theme-gold-rush::sections.voting.status_open') }}
            </p>
            <h2>{{ $heading }}</h2>

            @if (filled($deadline) && ! $isClosed)
                <p
                    class="sbs-countdown"
                    data-deadline="{{ $deadline }}"
                >
                    <span
                        class="sbs-countdown-label"
                        >{{ $deadlineLabel }}</span
                    >
                    <span
                        class="sbs-countdown-value"
                        data-deadline-value
                        aria-live="polite"
                    >
                        &nbsp;
                    </span>
                </p>
                @include ('capell-theme-gold-rush::partials.countdown-script')
            @endif
        </div>
    </div>
</section>
