@php
    $items = data_get($section, 'items', []);
@endphp

<section class="rwi-section">
    <div class="rwi-section-inner">
        <p class="rwi-kicker">
            {{ __('capell-theme-raw-index::sections.proof.kicker') }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="rwi-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div
            class="rwi-grid"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                <article class="rwi-card">
                    <span
                        class="rwi-numeral"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3>
                        {{ data_get($item, 'value', data_get($item, 'title', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'label', data_get($item, 'summary', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
