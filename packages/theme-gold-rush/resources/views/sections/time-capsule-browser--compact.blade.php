@php
    $heading = data_get($section, 'heading', __('capell-theme-gold-rush::sections.capsule.heading'));
    $eras = collect(data_get($section, 'items', []))->take(50)->values();
@endphp

<section
    id="time-capsule-browser"
    class="sbs-section sbs-capsule-compact"
>
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ __('capell-theme-gold-rush::sections.capsule.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>

        <div class="sbs-capsule-rail sbs-capsule-rail-compact">
            @foreach ($eras as $era)
                @php
                    $previewItems = collect(data_get($era, 'items', []))->take(3)->values();
                @endphp

                <details
                    class="sbs-capsule sbs-capsule-small"
                    style="--sbs-capsule-depth: {{ $loop->index }}"
                >
                    <summary class="sbs-capsule-face">
                        <span class="sbs-capsule-year">
                            {{ data_get($era, 'title', data_get($era, 'name', '')) }}
                        </span>
                    </summary>

                    <div class="sbs-capsule-contents">
                        <ul class="sbs-capsule-preview-list">
                            @foreach ($previewItems as $previewItem)
                                <li class="sbs-capsule-preview-item">
                                    {{ data_get($previewItem, 'title', data_get($previewItem, 'name', '')) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
