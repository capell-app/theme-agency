@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.voting.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-gold-rush::sections.voting.summary'));
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

    $percentages = $tallies->map(
        fn (int $tally): float => $totalVotes > 0 ? round(($tally / $totalVotes) * 100, 1) : 0.0,
    );

    // Conic-gradient stops computed server-side from payload percentages —
    // deterministic, page-cache safe, never client-recomputed (§0.1).
    $gaugeStops = [];
    $runningAngle = 0.0;

    foreach ($percentages as $index => $percentage) {
        $startAngle = $runningAngle;
        $endAngle = $runningAngle + ($percentage * 3.6);
        $gaugeStops[] = sprintf(
            'var(--sbs-gauge-%d, var(--sbs-accent)) %.1fdeg %.1fdeg',
            $index % 4,
            $startAngle,
            $endAngle,
        );
        $runningAngle = $endAngle;
    }

    $gaugeGradient = $gaugeStops === []
        ? 'var(--sbs-line) 0deg 360deg'
        : implode(', ', $gaugeStops);

    $leaderPercentage = $percentages->first() ?? 0.0;
@endphp

<section
    id="voting-status"
    class="sbs-section"
>
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-gold-rush::sections.voting.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="sbs-lede">{{ $summary }}</p>

        <div class="sbs-vote-panel">
            <div class="sbs-vote-status-bar">
                <span class="sbs-vote-status-pill">
                    {{ $isClosed
                        ? __('capell-theme-gold-rush::sections.voting.status_closed')
                        : __('capell-theme-gold-rush::sections.voting.status_open') }}
                </span>

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

            <div class="sbs-vote-gauge-row">
                <div
                    class="sbs-vote-gauge"
                    style="--sbs-gauge-gradient: {{ $gaugeGradient }}"
                    role="img"
                    aria-label="{{ __('capell-theme-gold-rush::sections.voting.gauge_label', ['percentage' => $leaderPercentage]) }}"
                >
                    <span class="sbs-vote-gauge-value">
                        {{ rtrim(rtrim(number_format($leaderPercentage, 1), '0'), '.') }}%
                    </span>
                    <span class="sbs-vote-gauge-caption">
                        {{ __('capell-theme-gold-rush::sections.voting.gauge_caption') }}
                    </span>
                </div>

                <ul class="sbs-vote-gauge-legend">
                    @foreach ($items as $item)
                        <li class="sbs-vote-gauge-legend-item">
                            <span
                                class="sbs-vote-gauge-swatch"
                                style="--sbs-swatch-index: {{ $loop->index % 4 }}"
                                aria-hidden="true"
                            ></span>
                            <span
                                >{{ data_get($item, 'title', data_get($item, 'name', '')) }}</span
                            >
                            <span class="sbs-vote-gauge-legend-value">
                                {{ $percentages[$loop->index] ?? 0 }}%
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="sbs-vote-rows">
                @foreach ($items as $item)
                    @php
                        $itemMeta = (string) data_get($item, 'meta', '');
                        $tally = null;

                        if (preg_match('/\d[\d,]*/', $itemMeta, $matches) === 1) {
                            $tally = $matches[0];
                        }
                    @endphp

                    <div class="sbs-vote-row">
                        <span class="sbs-vote-position">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div>
                            <p class="sbs-vote-title">
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </p>
                            <p class="sbs-vote-meta">
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        </div>
                        <span class="sbs-vote-tally">
                            {{ $tally ?? $itemMeta }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
