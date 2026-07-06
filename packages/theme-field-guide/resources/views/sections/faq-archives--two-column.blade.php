{{--
    faq-archives-accordion (two-column variant): same shared
    `accordion-toggle.js` markup contract as the default view, split across
    two independent accordion containers in "multi" mode so a reader can have
    one question open in each column at once — useful once the archive FAQ
    grows past a handful of entries.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.faq.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-field-guide::sections.faq.summary'));
    $fallbackItems = __('capell-theme-field-guide::sections.faq.items');
    $items = collect(data_get($section, 'items', is_array($fallbackItems) ? $fallbackItems : []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();
    $columns = $items->values()->chunk((int) ceil(max($items->count(), 1) / 2));
@endphp

<section
    id="faq-archives"
    class="fga-section fga-section-panel"
    data-widget="faq-archives-accordion"
    data-variant="two-column"
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

        <div class="fga-faq-columns">
            @foreach ($columns as $columnIndex => $columnItems)
                <div
                    class="fga-faq"
                    data-accordion
                    data-accordion-mode="multi"
                >
                    @foreach ($columnItems as $item)
                        @php
                            $panelId = 'faq-archives-panel-' . $columnIndex . '-' . $loop->index;
                        @endphp
                        <div class="fga-faq-item">
                            <h3 class="fga-faq-heading">
                                <button
                                    type="button"
                                    class="fga-faq-trigger"
                                    data-accordion-trigger
                                    aria-controls="{{ $panelId }}"
                                    @if ($columnIndex === 0 && $loop->first) aria-expanded="true" @else aria-expanded="false" @endif
                                >
                                    <span
                                        >{{ data_get($item, 'title', data_get($item, 'name', '')) }}</span
                                    >
                                    <span
                                        class="fga-faq-trigger-icon"
                                        aria-hidden="true"
                                    ></span>
                                </button>
                            </h3>
                            <div
                                id="{{ $panelId }}"
                                class="fga-faq-panel"
                                data-accordion-panel
                                @if (! ($columnIndex === 0 && $loop->first)) hidden @endif
                            >
                                <p>
                                    {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>
