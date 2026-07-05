@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.criteria.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-gold-rush::sections.criteria.summary'));
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();

    $defaultWeights = [40, 30, 20, 10];
@endphp

<section
    id="score-criteria"
    class="sbs-section"
>
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-gold-rush::sections.criteria.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="sbs-lede">{{ $summary }}</p>

        <div class="sbs-criteria-list">
            @foreach ($items as $item)
                @php
                    $weight = data_get($item, 'weight', $defaultWeights[$loop->index] ?? 25);
                @endphp

                <div class="sbs-criteria-row">
                    <div class="sbs-criteria-head">
                        <span class="sbs-criteria-name">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </span>
                        <span class="sbs-criteria-weight">
                            {{ __('capell-theme-gold-rush::sections.criteria.weight_label') }} {{ $weight }}%
                        </span>
                    </div>
                    <div
                        class="sbs-criteria-bar-track"
                        role="img"
                        aria-label="{{ $weight }}%"
                    >
                        <span
                            class="sbs-criteria-bar-fill"
                            style="--sbs-bar-value: {{ $weight }}%"
                        ></span>
                    </div>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
