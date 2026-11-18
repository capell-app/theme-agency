@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
    $heading = data_get($section, 'heading');
@endphp

@if ($items->isNotEmpty())
    <section
        id="filters"
        class="ppc-section"
    >
        <div class="ppc-section-inner">
            @if (filled(data_get($section, 'kicker')))
                <p class="ppc-kicker">{{ data_get($section, 'kicker') }}</p>
            @endif

            @if (filled($heading))
                <h2>{{ $heading }}</h2>
            @endif

            <div class="ppc-filters">
                @foreach ($items as $item)
                    @php
                        $filterUrl = PublicUrlSanitizer::sanitize(data_get($item, 'url', data_get($item, 'href')));
                        $chipTag = filled($filterUrl) ? 'a' : 'span';
                        $count = data_get($item, 'count');
                    @endphp

                    <{{ $chipTag }}
                        class="ppc-filter-chip"
                        @if (filled($filterUrl)) href="{{ $filterUrl }}" @endif
                    >
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        @if (filled($count))
                            <span
                                class="ppc-filter-chip-count"
                                >{{ $count }}</span
                            >
                        @endif
                    </{{ $chipTag }}>
                @endforeach
            </div>

            <div class="ppc-grid">
                @foreach ($items as $item)
                    <article class="ppc-card">
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        @if (filled(data_get($item, 'summary')))
                            <p>{{ data_get($item, 'summary') }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
