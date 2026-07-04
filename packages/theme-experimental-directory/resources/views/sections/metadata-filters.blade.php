@php
    $groups = data_get($section, 'items', data_get($section, 'groups', [
        ['title' => __('capell-theme-experimental-directory::sections.filters.medium_label'), 'chips' => []],
    ]));
@endphp

<section
    id="metadata-filters"
    class="exd-section exd-section-raised"
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-experimental-directory::sections.filters.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-experimental-directory::sections.filters.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="exd-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="exd-chip-groups">
            @foreach ($groups as $group)
                <div>
                    <p class="exd-chip-group-label">
                        {{ data_get($group, 'title', data_get($group, 'label', '')) }}
                    </p>
                    <div class="exd-chip-row">
                        @foreach (data_get($group, 'chips', data_get($group, 'items', [])) as $chip)
                            <span class="exd-chip">
                                {{ data_get($chip, 'label', is_string($chip) ? $chip : '') }}
                                @if (filled(data_get($chip, 'count')))
                                    <span class="exd-chip-count">
                                        {{ data_get($chip, 'count') }}
                                    </span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
