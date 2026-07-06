@php
    /**
     * featured-project-showcase --stacked variant: the still runs full-width
     * above the synopsis/credits column, for narrower detail-page layouts
     * where the side-by-side default grid would squeeze the still too small
     * to read as a genuine cover frame.
     */
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.featured_project.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.featured_project.summary'));
    $mediaUrl = data_get($section, 'image', data_get($section, 'imageUrl'));
    $mediaAlt = data_get($section, 'imageAlt', __('capell-theme-reel-room::sections.featured_project.still_alt'));
    $itemUrl = data_get($section, 'url', data_get($section, 'href'));
    $items = data_get($section, 'items', []);
    $credits = data_get($section, 'credits', []);
@endphp

<section
    id="featured-project"
    class="mva-section mva-spotlight"
    data-widget="featured-project-showcase"
    data-variant="stacked"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.featured_project.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        <div
            class="mva-spotlight-still mva-spotlight-still-stacked"
            style="margin-top: 2rem"
        >
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

        <div
            class="mva-spotlight-breakdown mva-spotlight-breakdown-stacked"
            style="margin-top: 2rem"
        >
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

            @if (is_iterable($credits) && collect($credits)->isNotEmpty())
                <div class="mva-spotlight-credits">
                    <p class="mva-spotlight-credits-heading">
                        {{ __('capell-theme-reel-room::sections.featured_project.credits_heading') }}
                    </p>
                    <ul class="mva-spotlight-credits-list">
                        @foreach ($credits as $credit)
                            <li>
                                <span class="mva-spotlight-credit-role">
                                    {{ data_get($credit, 'role', '') }}
                                </span>
                                <span class="mva-spotlight-credit-name">
                                    {{ data_get($credit, 'name', '') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
</section>
