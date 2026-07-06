@php
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.pulse.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.pulse.summary'));
    $stats = collect(data_get($section, 'stats', []))->take(4);
    $bars = collect(data_get($section, 'bars', data_get($section, 'sevenDay', [])))->take(7)->values();
    $maxBarValue = max(1, (int) $bars->max('value'));
@endphp

<section
    id="submission-pulse"
    class="exd-section exd-section-raised"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.pulse.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        @if ($stats->isNotEmpty())
            <div class="exd-pulse-stat-row">
                @foreach ($stats as $stat)
                    <div class="exd-pulse-stat">
                        <span class="exd-pulse-stat-value">
                            {{ data_get($stat, 'value', '') }}
                        </span>
                        <span class="exd-pulse-stat-label">
                            {{ data_get($stat, 'label', '') }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($bars->isNotEmpty())
            <div
                class="exd-pulse-sparkline"
                data-submission-pulse
                role="img"
                aria-label="{{ __('capell-theme-wild-card::sections.pulse.chart_label') }}"
            >
                @foreach ($bars as $bar)
                    @php
                        $barValue = (int) data_get($bar, 'value', 0);
                        $barHeightPercent = (int) round(($barValue / $maxBarValue) * 100);
                    @endphp

                    <span
                        class="exd-pulse-bar"
                        style="--exd-pulse-bar-height: {{ $barHeightPercent }}%"
                    >
                        <span class="exd-pulse-bar-value">{{ $barValue }}</span>
                        <span class="exd-pulse-bar-label">
                            {{ data_get($bar, 'label', '') }}
                        </span>
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</section>
