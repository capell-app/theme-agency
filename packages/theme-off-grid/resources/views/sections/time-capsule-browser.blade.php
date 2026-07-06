{{--
    time-capsule-browser (Part 2 §C cross-archive addition, off-grid skin):
    archive eras rendered as 3D-perspective capsules (CSS `perspective` +
    `rotateY`, off-grid's own monospace/brutalist tokens). Hovering or
    focusing a capsule previews its 3-5 items; clicking (a same-page radio
    input driving a pure-CSS `:has()` selector, no JS) expands exactly one
    capsule at a time. Guardrail §0.8: `:has()` is enhancement-only --
    browsers without support simply show every era's preview list already
    expanded, so nothing is ever hidden behind unsupported CSS. Guardrail
    §0.6: this widget still honours the theme's `motionIntensity: none` tier
    -- the perspective tilt and expand transition both collapse to instant,
    non-animated state changes when `prefers-reduced-motion: reduce` is set
    or the resolved token is `none` (see `[data-motion-intensity="none"]` in
    theme-off-grid.css), never to an absence of the 3D composition itself.
    Payload cap §0.3: capped at 6 eras, 5 preview items each.
--}}

@php
    $heading = data_get($section, 'heading', __('capell-theme-off-grid::sections.capsules.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-off-grid::sections.capsules.summary'));
    $eras = collect(data_get($section, 'items', data_get($section, 'eras', [])))->take(6);
    $groupName = 'rwi-capsule-' . data_get($section, 'key', 'time-capsule-browser');
@endphp

<section
    id="time-capsule-browser"
    class="rwi-section"
    data-widget="time-capsule-browser"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">07</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.capsules.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="rwi-lede">{{ $summary }}</p>

        <div
            class="rwi-capsule-rack"
            style="margin-top: 2rem"
        >
            @foreach ($eras as $era)
                @php
                    $eraTitle = (string) data_get($era, 'title', data_get($era, 'name', ''));
                    $eraSummary = (string) data_get($era, 'summary', data_get($era, 'description', ''));
                    $eraItems = collect(data_get($era, 'items', []))->take(5);
                    $inputId = $groupName . '-' . $loop->index;
                @endphp

                <div class="rwi-capsule">
                    <input
                        type="radio"
                        name="{{ $groupName }}"
                        id="{{ $inputId }}"
                        class="rwi-capsule-toggle"
                        aria-label="{{ __('capell-theme-off-grid::sections.capsules.expand_label', ['era' => $eraTitle]) }}"
                        @checked ($loop->first)
                    />

                    <article
                        class="rwi-capsule-face"
                        style="--rwi-capsule-depth: {{ $loop->index }};"
                    >
                        <label
                            for="{{ $inputId }}"
                            class="rwi-capsule-label"
                        >
                            <span
                                class="rwi-index-numeral"
                                aria-hidden="true"
                            >
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span
                                class="rwi-capsule-title"
                                >{{ $eraTitle }}</span
                            >
                        </label>
                        <p class="rwi-meta">{{ $eraSummary }}</p>

                        <ul class="rwi-capsule-preview">
                            @foreach ($eraItems as $previewItem)
                                <li>
                                    {{ data_get($previewItem, 'title', data_get($previewItem, 'name', '')) }}
                                </li>
                            @endforeach
                        </ul>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
