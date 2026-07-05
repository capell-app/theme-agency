@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.faq.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-field-guide::sections.faq.summary'));
    $fallbackItems = __('capell-theme-field-guide::sections.faq.items');
    $items = collect(data_get($section, 'items', is_array($fallbackItems) ? $fallbackItems : []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
@endphp

<section
    id="faq-archives"
    class="fga-section fga-section-panel"
>
    <div class="fga-section-inner">
        <div class="fga-section-head">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-field-guide::sections.faq.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="fga-lede">{{ $summary }}</p>
            </div>
        </div>

        <div class="fga-faq">
            @foreach ($items as $item)
                <details
                    name="fga-faq"
                    @if ($loop->first) open @endif
                >
                    <summary>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </summary>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                </details>
            @endforeach
        </div>
    </div>
</section>
