@php
    $items = data_get($section, 'items', []);
@endphp

<section class="mva-section">
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.proof.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-reel-room::sections.proof.heading')) }}
        </h2>
        <div
            class="mva-proof-grid"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                <article class="mva-proof-item">
                    <p class="mva-proof-value">
                        {{ data_get($item, 'value', data_get($item, 'title', '')) }}
                    </p>
                    <p>
                        {{ data_get($item, 'label', data_get($item, 'summary', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
