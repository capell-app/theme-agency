{{--
    faq-archives-accordion (Wave 4c signature widget #4): markup follows the
    shared Wave 2.6 `accordion-toggle.js` module contract exactly — a
    `[data-accordion]` container (single-open mode), `button[data-accordion-trigger]`
    with `aria-controls` pointing at each panel, and `[data-accordion-panel]`
    with a matching `id`. No new accordion JavaScript is written here; the
    module manages `aria-expanded` and `hidden` once it initialises, and the
    first item's trigger is authored with `aria-expanded="true"` so it starts
    open even before the script runs.
--}}
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
    data-widget="faq-archives-accordion"
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

        <div
            class="fga-faq"
            data-accordion
            data-accordion-mode="single"
        >
            @foreach ($items as $item)
                @php
                    $panelId = 'faq-archives-panel-' . $loop->index;
                @endphp
                <div class="fga-faq-item">
                    <h3 class="fga-faq-heading">
                        <button
                            type="button"
                            class="fga-faq-trigger"
                            data-accordion-trigger
                            aria-controls="{{ $panelId }}"
                            @if ($loop->first) aria-expanded="true" @else aria-expanded="false" @endif
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
                        @if (! $loop->first) hidden @endif
                    >
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
