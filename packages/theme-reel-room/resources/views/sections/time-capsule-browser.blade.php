@php
    /**
     * time-capsule-browser (Wave 4b cross-archive mechanic — Part 2 §C: "one
     * implementation, four token skins" shared in structure across
     * wild-card, off-grid, gold-rush, and reel-room, each theme building its
     * own independent implementation in parallel using its own token names).
     * Reel Room's skin renders each archive era as a 3D-perspective capsule
     * (CSS `perspective` + `rotateY`) using this theme's own `--mva-*`
     * tokens. Hover previews 3-5 items inside the capsule via plain CSS;
     * clicking a capsule's radio input expands it using `:has()` — guardrail
     * §0.8: `:has()` is enhancement-only, so browsers without it simply keep
     * every capsule in its resting, unexpanded state, which is still a
     * complete, legible list of eras and their preview items (the radio
     * inputs remain keyboard-operable regardless of `:has()` support).
     * Payload cap §0.3: eras capped at 20 (carousel-scale cap), preview
     * items per era capped at 5 per the mechanic's own spec.
     */
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.time_capsule_browser.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.time_capsule_browser.summary'));
    $eras = collect(data_get($section, 'items', data_get($section, 'eras', [])))->take(20);
    $groupName = 'mva-time-capsule-' . substr(md5($heading), 0, 8);
@endphp

<section
    id="time-capsule-browser"
    class="mva-section mva-section-raised"
    data-widget="time-capsule-browser"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.time_capsule_browser.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        @if ($eras->isNotEmpty())
            <div
                class="mva-capsule-rack"
                style="margin-top: 2rem"
            >
                @foreach ($eras as $era)
                    @php
                        $previewItems = collect(data_get($era, 'previewItems', data_get($era, 'items', [])))->take(5);
                        $inputId = $groupName . '-' . $loop->index;
                    @endphp

                    <div class="mva-capsule">
                        <input
                            type="radio"
                            name="{{ $groupName }}"
                            id="{{ $inputId }}"
                            class="mva-capsule-toggle"
                            @checked ($loop->first)
                        />
                        <label
                            for="{{ $inputId }}"
                            class="mva-capsule-shell"
                        >
                            <span class="mva-capsule-era">
                                {{ data_get($era, 'title', data_get($era, 'name', '')) }}
                            </span>
                            <span class="mva-capsule-summary">
                                {{ data_get($era, 'summary', data_get($era, 'description', '')) }}
                            </span>

                            @if ($previewItems->isNotEmpty())
                                <span class="mva-capsule-preview">
                                    @foreach ($previewItems as $previewItem)
                                        <span class="mva-capsule-preview-item">
                                            {{ data_get($previewItem, 'title', data_get($previewItem, 'name', '')) }}
                                        </span>
                                    @endforeach
                                </span>
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
