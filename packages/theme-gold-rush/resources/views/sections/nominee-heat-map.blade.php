@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.heatmap.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-gold-rush::sections.heatmap.summary'));
    // Payload cap (Wave 2 §0.3): grids ship at most 50 cells.
    $items = collect(data_get($section, 'items', []))->take(50)->values();
@endphp

<section
    id="nominee-heat-map"
    class="sbs-section"
>
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-gold-rush::sections.heatmap.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="sbs-lede">{{ $summary }}</p>

        <div
            class="sbs-heat-grid"
            role="img"
            aria-label="{{ __('capell-theme-gold-rush::sections.heatmap.aria_label') }}"
        >
            @foreach ($items as $item)
                @php
                    // Deterministic colour intensity from the payload score —
                    // no client-side randomness (Wave 2 §0.1).
                    $score = (float) data_get($item, 'score', 0);
                    $intensity = max(0.0, min(1.0, $score / 10));
                @endphp

                <div
                    class="sbs-heat-cell"
                    style="--sbs-heat-intensity: {{ round($intensity, 2) }}"
                >
                    <span
                        class="sbs-heat-score"
                        >{{ number_format($score, 1) }}</span
                    >
                    <span class="sbs-heat-title">
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </span>
                    <span class="sbs-heat-meta">
                        {{ data_get($item, 'meta', data_get($item, 'category', '')) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</section>
