@php
    $heading = data_get($section, 'heading', __('capell-theme-off-grid::sections.markers.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-off-grid::sections.markers.summary'));
    $items = data_get($section, 'items', []);
    $label = data_get($section, 'label', __('capell-theme-off-grid::sections.markers.submit_button'));
    $url = data_get($section, 'url', '/');
@endphp

<section
    id="submission-markers"
    class="rwi-section rwi-section-dark"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">04</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.markers.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="rwi-lede">{{ $summary }}</p>

        <div
            class="rwi-marker-grid"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                <article
                    class="rwi-marker"
                    data-stamp="{{ data_get($item, 'stamp', __('capell-theme-off-grid::sections.markers.kicker')) }}"
                >
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>

        <div class="rwi-actions">
            <a
                class="rwi-button"
                href="{{ $url }}"
            >
                {{ $label }}
            </a>
        </div>
    </div>
</section>
