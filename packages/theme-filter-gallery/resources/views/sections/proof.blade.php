@php
    $heading = data_get($section, 'heading', __('capell-theme-filter-gallery::sections.proof.heading'));
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'value', data_get($item, 'title'))))
        ->values();
@endphp

<section
    id="proof"
    class="fga-section"
>
    <div class="fga-section-inner fga-section-inner-tight">
        <div class="fga-section-head">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-filter-gallery::sections.proof.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
            </div>
        </div>

        <div class="fga-stat-strip">
            @foreach ($items as $item)
                <article class="fga-stat">
                    <span class="fga-stat-value">
                        {{ data_get($item, 'value', data_get($item, 'title', '')) }}
                    </span>
                    <p>
                        {{ data_get($item, 'label', data_get($item, 'summary', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
