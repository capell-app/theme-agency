{{--
    portfolio-grid-cards — the roster grid that role-filters-toolbar filters.
    Each card is a `[data-roster-card]` with `data-discipline` and
    `data-available` attributes the toolbar's inline script reads to toggle
    `hidden`. Capped at fifty roster entries per §0.3's client-filterable-list
    cap. Distinct from the existing generic `portfolio-grid` section: this one
    is purpose-built as the filter target, one discipline value per card
    matching the toolbar's facet keys.
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
    class="pfd-section"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.grid_cards.eyebrow')) }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="pfd-lede">{{ $summary }}</p>

        <div
            id="portfolio-grid-cards-list"
            class="pfd-cards"
        >
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemDiscipline = (string) data_get($item, 'discipline', data_get($item, 'category', ''));
                    $itemAvailable = (bool) data_get($item, 'available', false);
                @endphp

                <article
                    class="pfd-card"
                    data-roster-card
                    data-discipline="{{ $itemDiscipline }}"
                    data-available="{{ $itemAvailable ? 'true' : 'false' }}"
                >
                    <div class="pfd-card-head">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="pfd-photo pfd-avatar"
                            />
                        @else
                            <div
                                class="pfd-photo pfd-photo-empty pfd-avatar"
                                aria-hidden="true"
                            ></div>
                        @endif
                        <div>
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
                            <p class="pfd-role">
                                {{ data_get($item, 'meta', data_get($item, 'roleLabel', '')) }}
                            </p>
                        </div>
                    </div>

                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>

                    <p class="pfd-meta">
                        @if (filled(data_get($item, 'location')))
                            <span>{{ data_get($item, 'location') }}</span>
                        @endif

                        @if ($itemAvailable)
                            <span>
                                <span
                                    class="pfd-dot"
                                    aria-hidden="true"
                                ></span>
                                {{ __('capell-theme-deep-bench::sections.grid_cards.available') }}
                            </span>
                        @endif
                    </p>
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
