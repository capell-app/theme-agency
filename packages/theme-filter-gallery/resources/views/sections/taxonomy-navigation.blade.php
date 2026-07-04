@php
    $heading = data_get($section, 'heading', __('capell-theme-filter-gallery::sections.taxonomy.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-filter-gallery::sections.taxonomy.summary'));
    $fallbackGroups = __('capell-theme-filter-gallery::sections.taxonomy.groups');
    $fallbackGroups = is_array($fallbackGroups) ? $fallbackGroups : [];
    $groups = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $group): bool => filled(data_get($group, 'title')))
        ->values();
    $facetKeys = ['type', 'style', 'colour', 'industry'];
@endphp

<section
    id="taxonomy-navigation"
    class="fga-section"
>
    <div class="fga-section-inner">
        <div class="fga-section-head">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-filter-gallery::sections.taxonomy.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="fga-lede">{{ $summary }}</p>
            </div>
            <p class="fga-mono-note">
                {{ __('capell-theme-filter-gallery::sections.taxonomy.count_note') }}
            </p>
        </div>

        <div class="fga-facet-board">
            @foreach ($groups->isNotEmpty() ? $groups : collect($fallbackGroups) as $group)
                @php
                    $fallbackGroup = $fallbackGroups[$loop->index % max(count($fallbackGroups), 1)] ?? [];
                    $facet = data_get($group, 'facet', data_get($fallbackGroup, 'facet', $facetKeys[$loop->index % count($facetKeys)]));
                    $groupTitle = data_get($group, 'title', data_get($fallbackGroup, 'title', ''));
                    $groupNote = data_get($group, 'summary', data_get($group, 'note', data_get($fallbackGroup, 'note', '')));
                    $chips = data_get($group, 'chips', data_get($group, 'links', data_get($fallbackGroup, 'chips', [])));
                @endphp

                <div class="fga-facet-group">
                    <div class="fga-facet-group-head">
                        <h3>{{ $groupTitle }}</h3>
                        <span class="fga-mono-note">{{ $facet }}</span>
                    </div>
                    <p>{{ $groupNote }}</p>
                    <ul class="fga-chip-row">
                        @foreach ($chips as $chip)
                            <li>
                                <a
                                    class="fga-chip fga-chip-{{ $facet }}"
                                    href="{{ data_get($chip, 'url', '#latest-designs') }}"
                                >
                                    {{ data_get($chip, 'label', '') }}
                                    <span class="fga-chip-count">
                                        {{ data_get($chip, 'count', '') }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>
