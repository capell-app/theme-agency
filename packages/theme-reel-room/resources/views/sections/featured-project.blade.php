@php
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.featured_project.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.featured_project.summary'));
    $mediaUrl = data_get($section, 'image', data_get($section, 'imageUrl'));
    $mediaAlt = data_get($section, 'imageAlt', __('capell-theme-reel-room::sections.featured_project.still_alt'));
    $itemUrl = data_get($section, 'url', data_get($section, 'href'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="featured-project"
    class="mva-section mva-spotlight"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.featured_project.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        <div
            class="mva-spotlight-grid"
            style="margin-top: 2rem"
        >
            <div class="mva-spotlight-still">
                @if (filled($mediaUrl))
                    <img
                        src="{{ $mediaUrl }}"
                        alt="{{ $mediaAlt }}"
                        loading="lazy"
                        decoding="async"
                        class="mva-spotlight-still-image"
                    />
                @endif
            </div>

            <div class="mva-spotlight-breakdown">
                @foreach ($items as $item)
                    <article class="mva-spotlight-item">
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach

                @if (filled($itemUrl))
                    <a
                        class="mva-button"
                        href="{{ $itemUrl }}"
                    >
                        {{ data_get($section, 'label', __('capell-theme-reel-room::sections.featured_project.open_label')) }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
