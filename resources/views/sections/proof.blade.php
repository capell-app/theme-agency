@php
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'value', data_get($item, 'title'))) || filled(data_get($item, 'label', data_get($item, 'summary'))))
        ->values();
@endphp

@if ($items->isNotEmpty())
    <section class="ppc-section">
        <div class="ppc-section-inner">
            @if (filled(data_get($section, 'kicker')))
                <p class="ppc-kicker">{{ data_get($section, 'kicker') }}</p>
            @endif

            @if (filled(data_get($section, 'heading')))
                <h2>{{ data_get($section, 'heading') }}</h2>
            @endif

            @if (filled(data_get($section, 'summary')))
                <p class="ppc-lede">{{ data_get($section, 'summary') }}</p>
            @endif

            <div class="ppc-grid">
                @foreach ($items as $item)
                    <article class="ppc-card">
                        @if (filled(data_get($item, 'value', data_get($item, 'title'))))
                            <h3>
                                {{ data_get($item, 'value', data_get($item, 'title')) }}
                            </h3>
                        @endif

                        @if (filled(data_get($item, 'label', data_get($item, 'summary'))))
                            <p>{{ data_get($item, 'label', data_get($item, 'summary')) }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
