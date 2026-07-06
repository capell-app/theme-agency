{{--
    Signature widget: columnists-with-latest-essay-preview (Part 2 §B,
    far-field). A columnist roster where each entry previews their latest
    piece (title, dek, and published date) alongside the columnist's beat --
    turning the plain byline grid into a browsable "what are they writing
    now" index.
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
    $variant = (string) data_get($section, 'variant', 'default');
@endphp

<section
    class="gcm-section gcm-section-tinted"
    id="columnists"
    data-widget="columnists-with-latest-essay-preview"
    data-variant="{{ $variant }}"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.columnists.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="gcm-lede">{{ $summary }}</p>
        </div>
        <div class="gcm-grid gcm-grid-3">
            @foreach ($items as $item)
                @php
                    $columnTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                    $columnUrl = (string) data_get($item, 'url', data_get($item, 'href', ''));
                    $latestTitle = (string) data_get($item, 'latest_title', '');
                    $latestUrl = (string) data_get($item, 'latest_url', $columnUrl);
                    $latestDate = (string) data_get($item, 'latest_date', '');
                @endphp

                <article class="gcm-card gcm-columnist-card">
                    <span
                        class="gcm-byline-mark"
                        aria-hidden="true"
                    >
                        {{ mb_substr(trim($columnTitle) !== '' ? trim($columnTitle) : 'A', 0, 1) }}
                    </span>
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
                        <div class="gcm-columnist-latest">
                            <p class="gcm-meta">
                                {{ __('capell-theme-far-field::sections.columnists.latest_label') }}
                                @if ($latestDate !== '')
                                    &middot;
                                    <time
                                        datetime="{{ $latestDate }}"
                                        >{{ $latestDate }}</time
                                    >
                                @endif
                            </p>
                            <p class="gcm-columnist-latest-title">
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
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
