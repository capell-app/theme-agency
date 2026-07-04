@php
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'value', data_get($item, 'metric', data_get($item, 'title')))))
        ->values();
@endphp

<section class="mcf-section">
    <div class="mcf-section-inner">
        <p class="mcf-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-minimal-curation-feed::sections.proof.kicker')) }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="mcf-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="mcf-stats">
                @foreach ($items as $item)
                    <article class="mcf-stat">
                        <p class="mcf-stat-value">
                            {{ data_get($item, 'value', data_get($item, 'metric', data_get($item, 'title', ''))) }}
                        </p>
                        @if (filled(data_get($item, 'name')))
                            <p class="mcf-meta">
                                {{ data_get($item, 'name') }}
                            </p>
                        @endif

                        <p>
                            {{ data_get($item, 'label', data_get($item, 'quote', data_get($item, 'summary', ''))) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
