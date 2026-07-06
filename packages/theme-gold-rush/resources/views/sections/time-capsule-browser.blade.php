@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.capsule.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-gold-rush::sections.capsule.summary'));
    // Payload cap (Wave 2 §0.3): grids ship at most 50 items; each era itself
    // previews 3-5 nominees per §C cross-archive spec.
    $eras = collect(data_get($section, 'items', []))->take(50)->values();
@endphp

<section
    id="time-capsule-browser"
    class="sbs-section sbs-section-field"
>
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-gold-rush::sections.capsule.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="sbs-lede">{{ $summary }}</p>

        <div class="sbs-capsule-rail">
            @foreach ($eras as $era)
                @php
                    // Cap era previews at 5 (§C: "hover previews 3-5 items").
                    $previewItems = collect(data_get($era, 'items', []))->take(5)->values();
                @endphp

                <details
                    class="sbs-capsule"
                    style="--sbs-capsule-depth: {{ $loop->index }}"
                >
                    <summary class="sbs-capsule-face">
                        <span class="sbs-capsule-year">
                            {{ data_get($era, 'title', data_get($era, 'name', '')) }}
                        </span>
                        <span class="sbs-capsule-meta">
                            {{ data_get($era, 'meta', data_get($era, 'summary', '')) }}
                        </span>
                    </summary>

                    <div class="sbs-capsule-contents">
                        <ul class="sbs-capsule-preview-list">
                            @foreach ($previewItems as $previewItem)
                                <li class="sbs-capsule-preview-item">
                                    <span class="sbs-capsule-preview-title">
                                        {{ data_get($previewItem, 'title', data_get($previewItem, 'name', '')) }}
                                    </span>
                                    @if (filled(data_get($previewItem, 'meta')))
                                        <span class="sbs-capsule-preview-meta">
                                            {{ data_get($previewItem, 'meta') }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
