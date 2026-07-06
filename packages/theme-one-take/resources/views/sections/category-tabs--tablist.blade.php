{{--
    category-tabs-pagination "--tablist" variant (mechanic: dossier pages).
    Wires the shared Foundation `tabs.js` module contract directly: a
    `[role="tablist"]` of page-range tabs above `[role="tabpanel"]` folios,
    each panel holding a compact index of that category's one-pagers. No
    per-theme JS is written here — `tabs.js` (imported once by
    `capell-frontend.js`, Wave 2.6) discovers the markup and drives keyboard
    navigation, tabindex roving, and panel visibility.
--}}
@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-one-take::sections.topics.product_title'), 'summary' => __('capell-theme-one-take::sections.topics.product_summary'), 'range' => __('capell-theme-one-take::sections.topics.product_range')],
        ['title' => __('capell-theme-one-take::sections.topics.design_title'), 'summary' => __('capell-theme-one-take::sections.topics.design_summary'), 'range' => __('capell-theme-one-take::sections.topics.design_range')],
        ['title' => __('capell-theme-one-take::sections.topics.advice_title'), 'summary' => __('capell-theme-one-take::sections.topics.advice_summary'), 'range' => __('capell-theme-one-take::sections.topics.advice_range')],
        ['title' => __('capell-theme-one-take::sections.topics.culture_title'), 'summary' => __('capell-theme-one-take::sections.topics.culture_summary'), 'range' => __('capell-theme-one-take::sections.topics.culture_range')],
    ]);
@endphp

<section
    id="category-tabs"
    class="ops-section"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-take::sections.topics.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-one-take::sections.topics.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="ops-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div
            class="ops-tab-row ops-dossier-tablist"
            role="tablist"
            aria-label="{{ __('capell-theme-one-take::sections.topics.tablist_label') }}"
        >
            @foreach ($items as $index => $item)
                <button
                    type="button"
                    id="ops-dossier-tab-{{ $index }}"
                    class="ops-tab ops-dossier-tab"
                    role="tab"
                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                    aria-controls="ops-dossier-panel-{{ $index }}"
                    tabindex="{{ $index === 0 ? '0' : '-1' }}"
                >
                    <span
                        class="ops-dossier-tab-range"
                        aria-hidden="true"
                    >
                        {{ data_get($item, 'range', __('capell-theme-one-take::sections.topics.default_range')) }}
                    </span>
                    <strong>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </strong>
                    <span>{{ data_get($item, 'summary', '') }}</span>
                </button>
            @endforeach
        </div>

        @foreach ($items as $index => $item)
            <div
                id="ops-dossier-panel-{{ $index }}"
                class="ops-dossier-tabpanel"
                role="tabpanel"
                aria-labelledby="ops-dossier-tab-{{ $index }}"
                @if ($index !== 0) hidden @endif
            >
                <p class="ops-dossier-margin-note">
                    {{ __('capell-theme-one-take::sections.topics.panel_note', ['range' => data_get($item, 'range', __('capell-theme-one-take::sections.topics.default_range'))]) }}
                </p>
                <a
                    class="ops-title-link"
                    href="{{ data_get($item, 'url', data_get($item, 'href', '/#one-page-grid')) }}"
                >
                    {{ __('capell-theme-one-take::sections.topics.panel_cta', ['title' => data_get($item, 'title', data_get($item, 'name', ''))]) }}
                </a>
            </div>
        @endforeach
    </div>
</section>
