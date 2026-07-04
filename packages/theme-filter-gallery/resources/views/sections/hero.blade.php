@php
    $eyebrow = data_get($section, 'eyebrow', data_get($section, 'kicker', __('capell-theme-filter-gallery::sections.hero.kicker')));
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-filter-gallery::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-filter-gallery::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-filter-gallery::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '#latest-designs')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-filter-gallery::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '#taxonomy-navigation')),
                'style' => 'secondary',
            ],
        ]);
    }

    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
    $facetChips = __('capell-theme-filter-gallery::sections.hero.chips');
    $facetChips = is_array($facetChips) ? $facetChips : [];
@endphp

<section class="fga-section fga-section-panel">
    <div class="fga-section-inner fga-hero-grid">
        <div>
            <p class="fga-kicker">{{ $eyebrow }}</p>
            <h1 style="margin-top: 0.75rem">{{ $heading }}</h1>
            <p
                class="fga-lede"
                style="margin-top: 1rem"
            >
                {{ $summary }}
            </p>
            <div class="fga-actions">
                @foreach ($actions as $action)
                    <a
                        class="fga-button {{ data_get($action, 'style') === 'secondary' ? 'fga-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>

            <div class="fga-hero-facet-strip">
                <span class="fga-mono-note">
                    {{ __('capell-theme-filter-gallery::sections.hero.facets_label') }}
                </span>
                <ul class="fga-chip-row">
                    @foreach ($facetChips as $chip)
                        <li>
                            <a
                                class="fga-chip fga-chip-{{ data_get($chip, 'facet', 'type') }}"
                                href="#taxonomy-navigation"
                            >
                                {{ data_get($chip, 'label', '') }}
                                <span class="fga-chip-count">
                                    {{ data_get($chip, 'count', '') }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <article class="fga-capture">
            @if (filled($mediaUrl))
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?? $heading }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="fga-capture-media fga-capture-media-wide"
                />
            @else
                <div
                    class="fga-capture-media fga-capture-media-wide fga-capture-media-empty"
                    aria-hidden="true"
                ></div>
            @endif
            <div class="fga-capture-body">
                <div class="fga-capture-title-row">
                    <h3>{{ $mediaAlt ?? $eyebrow }}</h3>
                    <span class="fga-capture-index">
                        {{ __('capell-theme-filter-gallery::sections.hero.capture_label') }}
                        #12482
                    </span>
                </div>
                <p class="fga-capture-source">
                    {{ __('capell-theme-filter-gallery::sections.hero.media_caption') }}
                </p>
            </div>
        </article>
    </div>
</section>
