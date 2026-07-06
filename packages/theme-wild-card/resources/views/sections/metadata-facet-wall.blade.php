@php
    $groups = collect(data_get($section, 'items', data_get($section, 'groups', [
        ['title' => __('capell-theme-wild-card::sections.filters.medium_label'), 'chips' => []],
    ])))->take(12);
@endphp

<section
    id="metadata-facet-wall"
    class="exd-section exd-section-raised"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.filters.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-wild-card::sections.filters.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="exd-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div
            class="exd-facet-wall"
            data-metadata-facet-wall
        >
            @foreach ($groups as $groupIndex => $group)
                @php
                    $chips = collect(data_get($group, 'chips', data_get($group, 'items', [])))->take(50);
                    $groupLabel = data_get($group, 'title', data_get($group, 'label', ''));
                @endphp

                <fieldset class="exd-facet-column">
                    <legend class="exd-chip-group-label">
                        {{ $groupLabel }}
                    </legend>
                    <div class="exd-facet-options">
                        @foreach ($chips as $chipIndex => $chip)
                            @php
                                $chipLabel = data_get($chip, 'label', is_string($chip) ? $chip : '');
                                $chipCount = data_get($chip, 'count');
                                $facetId = 'facet-' . $groupIndex . '-' . $chipIndex;
                            @endphp

                            <label
                                class="exd-facet-option"
                                for="{{ $facetId }}"
                            >
                                <input
                                    type="checkbox"
                                    id="{{ $facetId }}"
                                    class="exd-facet-checkbox"
                                    name="facet[{{ $groupIndex }}][]"
                                    value="{{ $chipIndex }}"
                                />
                                <span
                                    class="exd-facet-label"
                                    >{{ $chipLabel }}</span
                                >
                                @if (filled($chipCount))
                                    <span
                                        class="exd-chip-count"
                                        >{{ $chipCount }}</span
                                    >
                                @endif
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>
    </div>
</section>
