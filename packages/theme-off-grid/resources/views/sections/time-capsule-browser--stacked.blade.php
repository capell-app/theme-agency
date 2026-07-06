{{--
    time-capsule-browser, `stacked` variant: the same radio-driven
    `:has()` expand mechanic without the 3D perspective rack -- a flat
    single-column stack of era capsules for narrower placements (e.g. a
    detail-page rail) where the depth composition would overflow.
--}}

@php
    $heading = data_get($section, 'heading', __('capell-theme-off-grid::sections.capsules.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-off-grid::sections.capsules.summary'));
    $eras = collect(data_get($section, 'items', data_get($section, 'eras', [])))->take(6);
    $groupName = 'rwi-capsule-' . data_get($section, 'key', 'time-capsule-browser') . '-stacked';
@endphp

<section
    id="time-capsule-browser"
    class="rwi-section"
    data-widget="time-capsule-browser"
    data-variant="stacked"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">07</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.capsules.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="rwi-lede">{{ $summary }}</p>

        <div
            class="rwi-capsule-rack rwi-capsule-rack-stacked"
            style="margin-top: 2rem"
        >
            @foreach ($eras as $era)
                @php
                    $eraTitle = (string) data_get($era, 'title', data_get($era, 'name', ''));
                    $eraSummary = (string) data_get($era, 'summary', data_get($era, 'description', ''));
                    $eraItems = collect(data_get($era, 'items', []))->take(5);
                    $inputId = $groupName . '-' . $loop->index;
                @endphp

                <div class="rwi-capsule rwi-capsule-stacked">
                    <input
                        type="radio"
                        name="{{ $groupName }}"
                        id="{{ $inputId }}"
                        class="rwi-capsule-toggle"
                        aria-label="{{ __('capell-theme-off-grid::sections.capsules.expand_label', ['era' => $eraTitle]) }}"
                        @checked ($loop->first)
                    />

                    <article class="rwi-capsule-face">
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
