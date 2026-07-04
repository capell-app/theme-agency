@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-scoreboard-showcase::sections.proof.value_default'), 'label' => __('capell-theme-scoreboard-showcase::sections.proof.label_default')],
    ]);
@endphp

<section class="sbs-section">
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-scoreboard-showcase::sections.proof.kicker') }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="sbs-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="sbs-proof-grid">
            @foreach ($items as $item)
                <article class="sbs-proof-card">
                    <p class="sbs-proof-value">
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
