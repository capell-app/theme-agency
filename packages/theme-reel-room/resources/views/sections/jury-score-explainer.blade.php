@php
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.jury_score_explainer.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.jury_score_explainer.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="jury-score-explainer"
    class="mva-section mva-section-raised"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.jury_score_explainer.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        <div
            class="mva-axes"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                @php
                    $meta = data_get($item, 'meta', '');
                    preg_match('/(\d+)\s*%/', (string) $meta, $matches);
                    $weight = isset($matches[1]) ? (int) $matches[1] : 25;
                @endphp

                <article class="mva-axis">
                    <div>
                        <p class="mva-axis-name">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </p>
                        <p class="mva-axis-weight">{{ $meta }}</p>
                    </div>
                    <div
                        class="mva-axis-track"
                        role="presentation"
                    >
                        <div
                            class="mva-axis-fill"
                            style="--mva-axis-score: {{ $weight }}%"
                        ></div>
                    </div>
                    <p class="mva-axis-score">{{ $weight }}%</p>
                </article>
                <p
                    class="mva-lede"
                    style="margin-top: -0.5rem"
                >
                    {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                </p>
            @endforeach
        </div>
    </div>
</section>
