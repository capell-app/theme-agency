@php
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
@endphp

<section
    id="source-metadata"
    class="mcf-section"
>
    <div class="mcf-section-inner">
        <p class="mcf-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-first-light::sections.metadata.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-first-light::sections.metadata.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="mcf-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if ($items->isNotEmpty())
            <div class="mcf-meta-rows">
                @foreach ($items as $item)
                    <div class="mcf-meta-row">
                        <p class="mcf-meta">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </p>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
