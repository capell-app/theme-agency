@php
    $groups = collect(data_get($section, 'items', data_get($section, 'groups', [])))->take(12);
@endphp

<section
    id="metadata-facet-wall"
    class="exd-section"
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
            class="exd-facet-wall exd-facet-wall-dense"
            data-metadata-facet-wall
        >
            @foreach ($groups as $groupIndex => $group)
                @php
                    $chips = collect(data_get($group, 'chips', data_get($group, 'items', [])))->take(50);
                @endphp

                <div class="exd-facet-dense-row">
                    <span class="exd-chip-group-label">
                        {{ data_get($group, 'title', data_get($group, 'label', '')) }}
                    </span>
                    <div class="exd-chip-row">
                        @foreach ($chips as $chipIndex => $chip)
                            @php
                                $chipLabel = data_get($chip, 'label', is_string($chip) ? $chip : '');
                                $chipCount = data_get($chip, 'count');
                            @endphp

                            <label class="exd-chip exd-facet-chip">
                                <input
                                    type="checkbox"
                                    class="exd-facet-checkbox-inline"
                                    name="facet[{{ $groupIndex }}][]"
                                    value="{{ $chipIndex }}"
                                />
                                {{ $chipLabel }}
                                @if (filled($chipCount))
                                    <span
                                        class="exd-chip-count"
                                        >{{ $chipCount }}</span
                                    >
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
