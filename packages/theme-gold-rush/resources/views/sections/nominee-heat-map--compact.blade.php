@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.heatmap.heading'));
    // Payload cap (Wave 2 §0.3): grids ship at most 50 cells.
    $items = collect(data_get($section, 'items', []))->take(50)->values();
@endphp

<section
    id="nominee-heat-map"
    class="sbs-section sbs-heat-compact"
>
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-gold-rush::sections.heatmap.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>

        <div
            class="sbs-heat-grid sbs-heat-grid-compact"
            role="img"
            aria-label="{{ __('capell-theme-gold-rush::sections.heatmap.aria_label') }}"
        >
            @foreach ($items as $item)
                @php
                    $score = (float) data_get($item, 'score', 0);
                    $intensity = max(0.0, min(1.0, $score / 10));
                @endphp

                <div
                    class="sbs-heat-cell sbs-heat-cell-compact"
                    style="--sbs-heat-intensity: {{ round($intensity, 2) }}"
                    title="{{ data_get($item, 'title', data_get($item, 'name', '')) }} — {{ number_format($score, 1) }}"
                >
                    <span
                        class="sbs-heat-score"
                        >{{ number_format($score, 1) }}</span
                    >
                </div>
            @endforeach
        </div>
    </div>
</section>
