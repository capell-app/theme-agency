@php
    /**
     * time-capsule-browser --void variant: the same expand-one-at-a-time
     * capsule rack, rendered without the raised panel background so it sits
     * directly against the archive's void surface — for pages that already
     * have a raised section immediately above or below and want the rack to
     * read as a distinct, darker recess instead of matching the surrounding
     * panel tone.
     */
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.time_capsule_browser.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.time_capsule_browser.summary'));
    $eras = collect(data_get($section, 'items', data_get($section, 'eras', [])))->take(20);
    $groupName = 'mva-time-capsule-void-' . substr(md5($heading), 0, 8);
@endphp

<section
    id="time-capsule-browser"
    class="mva-section"
    data-widget="time-capsule-browser"
    data-variant="void"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.time_capsule_browser.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        @if ($eras->isNotEmpty())
            <div
                class="mva-capsule-rack mva-capsule-rack-void"
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
