{{--
    portfolio-grid-cards--rows — same filterable roster + `[data-roster-card]`
    contract as the default grid, laid out as a dense scannable row list
    instead of a card grid (useful on directory/detail pages where the grid
    variant already appears elsewhere on the same page). Capped at fifty
    entries per §0.3.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-deep-bench::sections.grid_cards.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-deep-bench::sections.grid_cards.summary'));
    $items = collect(data_get($section, 'items', []))->take(50);

    if ($items->isEmpty()) {
        $items = collect([
            ['title' => __('capell-theme-deep-bench::sections.grid_cards.wells_title'), 'meta' => __('capell-theme-deep-bench::sections.grid_cards.wells_meta'), 'discipline' => 'product', 'available' => true],
        ]);
    }
@endphp

<section
    id="portfolio-grid-cards"
    class="pfd-section pfd-section-field"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.grid_cards.eyebrow')) }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="pfd-lede">{{ $summary }}</p>

        <div class="pfd-rail">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemDiscipline = (string) data_get($item, 'discipline', data_get($item, 'category', ''));
                    $itemAvailable = (bool) data_get($item, 'available', false);
                @endphp

                <article
                    class="pfd-rail-row"
                    data-roster-card
                    data-discipline="{{ $itemDiscipline }}"
                    data-available="{{ $itemAvailable ? 'true' : 'false' }}"
                >
                    <span
                        class="pfd-rail-number"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="pfd-title-link"
                                href="{{ $itemUrl }}"
                            >
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        @endif
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <span class="pfd-rail-meta">
                        {{ data_get($item, 'meta', data_get($item, 'roleLabel', '')) }}
                        @if ($itemAvailable)
                            &middot; {{ __('capell-theme-deep-bench::sections.grid_cards.available') }}
                        @endif
                    </span>
                </article>
            @endforeach
        </div>

        <p
            class="pfd-grid-cards-empty"
            data-portfolio-grid-cards-empty
            hidden
        >
            {{ __('capell-theme-deep-bench::sections.grid_cards.no_matches') }}
        </p>
    </div>
</section>
