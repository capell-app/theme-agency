@php
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.pulse.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.pulse.summary'));
    $stats = collect(data_get($section, 'stats', []))->take(4);
@endphp

<section
    id="submission-pulse"
    class="exd-section"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.pulse.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        @if ($stats->isNotEmpty())
            <div class="exd-pulse-stat-row exd-pulse-stat-row-compact">
                @foreach ($stats as $stat)
                    <div class="exd-pulse-stat exd-pulse-stat-compact">
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
    </div>
</section>
