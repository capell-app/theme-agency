@php
    /**
     * time-capsule-browser (Wave 4b cross-archive mechanic — Part 2 §C). Each
     * archive theme ships its own independent implementation of this mechanic
     * using its own token names and skin; this is Wild Card's arcade-cabinet
     * skin, where each past cycle is a cartridge slotted into a rack and
     * "loaded" into the cabinet by selecting its radio input. Hover and
     * selection previews are pure CSS: clicking a cartridge's radio expands
     * its preview via `:has()`.
     *
     * Guardrail §0.8: `:has()` is enhancement-only. Browsers without it keep
     * every cartridge in its resting, unexpanded state — still a complete,
     * legible list of cycles and their preview entries, with the radio inputs
     * fully keyboard-operable regardless of `:has()` support.
     *
     * Guardrail §0.3 payload caps: cycles capped at 20 (carousel-scale cap),
     * preview entries per cycle capped at 5 per the mechanic's own spec.
     */
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.time_capsule.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.time_capsule.summary'));
    $cycles = collect(data_get($section, 'items', data_get($section, 'cycles', [])))->take(20)->values();
    $groupName = 'exd-time-capsule-' . substr(md5($heading), 0, 8);
@endphp

<section
    id="time-capsule-browser"
    class="exd-section exd-time-capsule"
    data-time-capsule-browser
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.time_capsule.kicker') }}
        </p>
        <h2 class="exd-time-capsule-heading">{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        @if ($cycles->isNotEmpty())
            <div class="exd-time-capsule-rack">
                @foreach ($cycles as $cycle)
                    @php
                        $previewEntries = collect(data_get($cycle, 'previewItems', data_get($cycle, 'items', [])))->take(5);
                        $inputId = $groupName . '-' . $loop->index;
                    @endphp

                    <div class="exd-time-capsule-cartridge">
                        <input
                            type="radio"
                            name="{{ $groupName }}"
                            id="{{ $inputId }}"
                            class="exd-time-capsule-toggle"
                            @checked ($loop->first)
                        />
                        <label
                            for="{{ $inputId }}"
                            class="exd-time-capsule-shell"
                        >
                            <span class="exd-time-capsule-cycle">
                                {{ data_get($cycle, 'title', data_get($cycle, 'name', '')) }}
                            </span>
                            <span class="exd-time-capsule-summary">
                                {{ data_get($cycle, 'summary', data_get($cycle, 'description', '')) }}
                            </span>

                            @if ($previewEntries->isNotEmpty())
                                <span class="exd-time-capsule-preview">
                                    @foreach ($previewEntries as $previewEntry)
                                        <span
                                            class="exd-time-capsule-preview-item"
                                        >
                                            {{ data_get($previewEntry, 'title', data_get($previewEntry, 'name', '')) }}
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
