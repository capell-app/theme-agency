@php
    // launch-sequence-hero (Wave 4c signature widget): the headline, lede,
    // actions, and stat panel reveal in a scroll-staged sequence driven by
    // CSS scroll-driven animation (animation-timeline: view()) with
    // deterministic nth-child delays -- never Math.random() (§0.1). When
    // @supports (animation-timeline: view()) is unavailable, or the visitor
    // prefers reduced motion, every item is simply visible immediately: the
    // stagger is a progressive enhancement, not a requirement to read the
    // hero (§0.5/§0.6).
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-launch-pad::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-launch-pad::sections.hero.summary')));
    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow', __('capell-theme-launch-pad::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));

    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-launch-pad::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-launch-pad::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $panelRows = data_get($section, 'panelRows', [
        ['label' => __('capell-theme-launch-pad::sections.hero.panel_row_one_label'), 'value' => __('capell-theme-launch-pad::sections.hero.panel_row_one_value')],
        ['label' => __('capell-theme-launch-pad::sections.hero.panel_row_two_label'), 'value' => __('capell-theme-launch-pad::sections.hero.panel_row_two_value')],
        ['label' => __('capell-theme-launch-pad::sections.hero.panel_row_three_label'), 'value' => __('capell-theme-launch-pad::sections.hero.panel_row_three_value')],
    ]);
@endphp

<section class="lga-section lga-section-dark lga-sequence-hero">
    <div class="lga-section-inner lga-hero-grid">
        <div class="lga-sequence-stage">
            <p
                class="lga-eyebrow lga-sequence-step"
                style="--lga-step: 0"
            >{{ $kicker }}</p>
            <h1
                class="lga-sequence-step"
                style="--lga-step: 1"
            >
                {{ $heading }}
            </h1>
            <p
                class="lga-lede lga-sequence-step"
                style="--lga-step: 2"
            >{{ $summary }}</p>
            <div
                class="lga-actions lga-sequence-step"
                style="--lga-step: 3"
            >
                @foreach ($actions as $action)
                    <a
                        class="lga-button {{ data_get($action, 'style') === 'secondary' ? 'lga-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        @if (filled($mediaUrl))
            <img
                src="{{ $mediaUrl }}"
                alt="{{ $mediaAlt ?? $heading }}"
                width="960"
                height="1200"
                loading="eager"
                fetchpriority="high"
                decoding="async"
                class="lga-gallery-media lga-hero-media lga-sequence-step"
                style="box-shadow: var(--lga-card-shadow); --lga-step: 4"
            />
        @else
            <aside
                class="lga-card lga-sequence-step"
                style="--lga-step: 4"
            >
                <p class="lga-eyebrow">
                    {{ __('capell-theme-launch-pad::sections.hero.panel_kicker') }}
                </p>
                @foreach ($panelRows as $index => $row)
                    <div
                        class="lga-hero-panel-row lga-sequence-step"
                        style="--lga-step: {{ 5 + $index }}"
                    >
                        <span>{{ data_get($row, 'label') }}</span>
                        <span
                            class="lga-stat-value"
                            >{{ data_get($row, 'value') }}</span
                        >
                    </div>
                @endforeach
            </aside>
        @endif
    </div>
</section>
