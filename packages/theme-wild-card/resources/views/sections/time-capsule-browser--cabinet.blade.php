@php
    /**
     * time-capsule-browser --cabinet variant (Wave 2.2 sidecar-variant
     * convention). Same payload contract and guardrails as the base view
     * (see time-capsule-browser.blade.php): §0.8 `:has()` enhancement-only
     * with keyboard-operable radios, §0.3 caps of 20 cycles and 5 preview
     * entries. This variant lays the cartridges out as a horizontal
     * scroll-snap cabinet row rather than the base rack, for surfaces that
     * want the archive to read as a single shelf of machines.
     */
    $heading = data_get($section, 'heading', __('capell-theme-wild-card::sections.time_capsule.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-wild-card::sections.time_capsule.summary'));
    $cycles = collect(data_get($section, 'items', data_get($section, 'cycles', [])))->take(20)->values();
    $groupName = 'exd-time-capsule-cabinet-' . substr(md5($heading), 0, 8);
@endphp

<section
    id="time-capsule-browser"
    class="exd-section exd-time-capsule exd-time-capsule-cabinet"
    data-time-capsule-browser
>
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.time_capsule.kicker') }}
        </p>
        <h2 class="exd-time-capsule-heading">{{ $heading }}</h2>
        <p class="exd-lede">{{ $summary }}</p>

        @if ($cycles->isNotEmpty())
            <div class="exd-time-capsule-cabinet-row">
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
