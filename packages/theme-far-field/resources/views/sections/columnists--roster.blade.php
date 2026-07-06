{{--
    Variant: columnists-with-latest-essay-preview / roster. Same payload as
    the default grid, presented as a scannable index-row roster (byline mark,
    beat, and essay preview on one line) rather than a card grid -- for
    issues with a longer columnist bench than the default 3-up grid suits.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-far-field::sections.columnists.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-far-field::sections.columnists.summary'));
    $items = data_get($section, 'items', [
        [
            'title' => __('capell-theme-far-field::sections.columnists.editor_title'),
            'summary' => __('capell-theme-far-field::sections.columnists.editor_summary'),
            'latest_title' => __('capell-theme-far-field::sections.columnists.editor_latest_title'),
            'latest_date' => '2026-06-29',
        ],
        [
            'title' => __('capell-theme-far-field::sections.columnists.city_desk_title'),
            'summary' => __('capell-theme-far-field::sections.columnists.city_desk_summary'),
            'latest_title' => __('capell-theme-far-field::sections.columnists.city_desk_latest_title'),
            'latest_date' => '2026-06-27',
        ],
        [
            'title' => __('capell-theme-far-field::sections.columnists.critic_title'),
            'summary' => __('capell-theme-far-field::sections.columnists.critic_summary'),
            'latest_title' => __('capell-theme-far-field::sections.columnists.critic_latest_title'),
            'latest_date' => '2026-06-24',
        ],
    ]);
@endphp

<section
    class="gcm-section gcm-section-tinted"
    id="columnists"
    data-widget="columnists-with-latest-essay-preview"
    data-variant="roster"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.columnists.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="gcm-lede">{{ $summary }}</p>
        </div>

        <ol class="gcm-index gcm-columnist-roster">
            @foreach ($items as $item)
                @php
                    $columnTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                    $columnUrl = (string) data_get($item, 'url', data_get($item, 'href', ''));
                    $latestTitle = (string) data_get($item, 'latest_title', '');
                    $latestUrl = (string) data_get($item, 'latest_url', $columnUrl);
                    $latestDate = (string) data_get($item, 'latest_date', '');
                @endphp

                <li class="gcm-index-row">
                    <span
                        class="gcm-index-number"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <p class="gcm-meta">
                        {{ __('capell-theme-far-field::sections.columnists.kicker') }}
                    </p>
                    <div>
                        <h3>
                            @if ($columnUrl !== '')
                                <a
                                    class="gcm-title-link"
                                    href="{{ $columnUrl }}"
                                >
                                    {{ $columnTitle }}
                                </a>
                            @else
                                {{ $columnTitle }}
                            @endif
                        </h3>
                        <p>{{ data_get($item, 'summary', '') }}</p>

                        @if ($latestTitle !== '')
                            <p class="gcm-columnist-latest-inline">
                                <span class="gcm-meta">
                                    {{ __('capell-theme-far-field::sections.columnists.latest_label') }}
                                    @if ($latestDate !== '')
                                        &middot;
                                        <time
                                            datetime="{{ $latestDate }}"
                                            >{{ $latestDate }}</time
                                        >
                                    @endif
                                </span>
                                @if ($latestUrl !== '')
                                    <a
                                        class="gcm-title-link"
                                        href="{{ $latestUrl }}"
                                    >
                                        {{ $latestTitle }}
                                    </a>
                                @else
                                    {{ $latestTitle }}
                                @endif
                            </p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
