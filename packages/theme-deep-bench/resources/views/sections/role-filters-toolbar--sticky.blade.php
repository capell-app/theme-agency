{{--
    role-filters-toolbar--sticky — identical multi-select facet mechanic and
    identical inline script contract as the default view, pinned to the top
    of the viewport while the roster grid scrolls beneath it (CSS `position:
    sticky`, no JS scroll listener). Same client-side hidden/display toggling
    against `[data-roster-card]`; no drag-reorder, no fuzzy search.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-deep-bench::sections.filters_toolbar.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-deep-bench::sections.filters_toolbar.summary'));
    $facets = collect(data_get($section, 'facets', [
        ['key' => 'product', 'label' => __('capell-theme-deep-bench::sections.filters_toolbar.product'), 'meta' => '324'],
        ['key' => 'brand', 'label' => __('capell-theme-deep-bench::sections.filters_toolbar.brand'), 'meta' => '287'],
        ['key' => 'motion', 'label' => __('capell-theme-deep-bench::sections.filters_toolbar.motion'), 'meta' => '142'],
        ['key' => 'frontend', 'label' => __('capell-theme-deep-bench::sections.filters_toolbar.frontend'), 'meta' => '253'],
        ['key' => 'illustration', 'label' => __('capell-theme-deep-bench::sections.filters_toolbar.illustration'), 'meta' => '96'],
        ['key' => 'studios', 'label' => __('capell-theme-deep-bench::sections.filters_toolbar.studios'), 'meta' => '98'],
    ]))->take(20);
    $availabilityLabel = data_get($section, 'availabilityLabel', __('capell-theme-deep-bench::sections.filters_toolbar.availability'));
    $targetId = data_get($section, 'target', 'portfolio-grid-cards');
    $instanceId = 'role-filters-toolbar-sticky-' . data_get($section, 'instanceKey', 'default');
@endphp

<section
    id="role-filters-toolbar"
    class="pfd-section pfd-roster-toolbar-sticky-section"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.filters_toolbar.eyebrow')) }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="pfd-lede">{{ $summary }}</p>

        <form
            class="pfd-roster-toolbar pfd-roster-toolbar-sticky"
            data-role-filters-toolbar
            data-target="{{ $targetId }}"
            aria-label="{{ $heading }}"
        >
            <fieldset class="pfd-roster-toolbar-group">
                <legend class="pfd-roster-toolbar-legend">
                    {{ data_get($section, 'facetLegend', __('capell-theme-deep-bench::sections.filters_toolbar.legend')) }}
                </legend>

                @foreach ($facets as $index => $facet)
                    @php
                        $facetKey = data_get($facet, 'key', 'facet-' . $index);
                        $facetId = $instanceId . '-facet-' . $facetKey;
                    @endphp

                    <span class="pfd-roster-toolbar-option">
                        <input
                            type="checkbox"
                            id="{{ $facetId }}"
                            class="pfd-roster-toolbar-checkbox"
                            data-facet="{{ $facetKey }}"
                            data-facet-group="discipline"
                        />
                        <label
                            for="{{ $facetId }}"
                            class="pfd-roster-toolbar-label"
                        >
                            {{ data_get($facet, 'label', $facetKey) }}
                            @if (filled(data_get($facet, 'meta')))
                                <span
                                    class="pfd-roster-toolbar-meta"
                                    >{{ data_get($facet, 'meta') }}</span
                                >
                            @endif
                        </label>
                    </span>
                @endforeach
            </fieldset>

            <fieldset class="pfd-roster-toolbar-group">
                <legend class="pfd-roster-toolbar-legend">
                    {{ $availabilityLabel }}
                </legend>

                <span class="pfd-roster-toolbar-option">
                    <input
                        type="checkbox"
                        id="{{ $instanceId }}-available"
                        class="pfd-roster-toolbar-checkbox"
                        data-facet="available"
                        data-facet-group="availability"
                    />
                    <label
                        for="{{ $instanceId }}-available"
                        class="pfd-roster-toolbar-label"
                    >
                        {{ __('capell-theme-deep-bench::sections.filters_toolbar.open_to_work') }}
                    </label>
                </span>
            </fieldset>

            <button
                type="button"
                class="pfd-roster-toolbar-clear"
                data-role-filters-toolbar-clear
            >
                {{ __('capell-theme-deep-bench::sections.filters_toolbar.clear') }}
            </button>

            <p
                class="pfd-roster-toolbar-status"
                data-role-filters-toolbar-status
                aria-live="polite"
            ></p>
        </form>
    </div>
</section>

<script>
    ;(function () {
        var section = document.currentScript.previousElementSibling
        var toolbar =
            section && section.matches('form[data-role-filters-toolbar]')
                ? section
                : null
        if (!toolbar) {
            return
        }

        var targetId = toolbar.getAttribute('data-target')
        var target = targetId ? document.getElementById(targetId) : null
        var status = toolbar.querySelector('[data-role-filters-toolbar-status]')
        var clearButton = toolbar.querySelector('[data-role-filters-toolbar-clear]')
        var checkboxes = Array.prototype.slice.call(
            toolbar.querySelectorAll('.pfd-roster-toolbar-checkbox'),
        )

        function activeFacetsByGroup(groupName) {
            return checkboxes
                .filter(function (checkbox) {
                    return (
                        checkbox.checked &&
                        checkbox.getAttribute('data-facet-group') === groupName
                    )
                })
                .map(function (checkbox) {
                    return checkbox.getAttribute('data-facet')
                })
        }

        function apply() {
            if (!target) {
                return
            }

            var disciplines = activeFacetsByGroup('discipline')
            var requireAvailable = activeFacetsByGroup('availability').length > 0
            var cards = Array.prototype.slice.call(
                target.querySelectorAll('[data-roster-card]'),
            )
            var visibleCount = 0

            cards.forEach(function (card) {
                var cardDiscipline = card.getAttribute('data-discipline') || ''
                var cardAvailable = card.getAttribute('data-available') === 'true'
                var matchesDiscipline =
                    disciplines.length === 0 ||
                    disciplines.indexOf(cardDiscipline) !== -1
                var matchesAvailability = !requireAvailable || cardAvailable
                var isVisible = matchesDiscipline && matchesAvailability

                card.hidden = !isVisible

                if (isVisible) {
                    visibleCount += 1
                }
            })

            if (status) {
                status.textContent =
                    visibleCount +
                    ' / ' +
                    cards.length +
                    (status.getAttribute('data-suffix') || '')
            }
        }

        if (status) {
            status.setAttribute(
                'data-suffix',
                ' ' + '{{ __('capell-theme-deep-bench::sections.filters_toolbar.showing_suffix') }}',
            )
        }

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', apply)
        })

        if (clearButton) {
            clearButton.addEventListener('click', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = false
                })
                apply()
            })
        }

        apply()
    })()
</script>
