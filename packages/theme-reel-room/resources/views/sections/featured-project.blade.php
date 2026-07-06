@php
    /**
     * featured-project-showcase (Wave 4b signature widget): the spotlight
     * panel for the archive's headline project. Credits are absorbed inline
     * via the optional `credits` payload key rather than steering visitors
     * to a standalone credits sidebar (Part 2 §C / §F: a standalone
     * `media-credits-sidebar` for reel-room is killed as stale — the
     * fleet-wide `media-credits` grid section stays registered for a
     * dedicated credits page, but this showcase no longer depends on it to
     * tell a complete story of who made the featured work).
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
    </div>
</section>
