@php
    $eyebrow = (string) ($widget->getMeta('eyebrow') ?? __('capell-theme-reading-room::sections.search_hero.eyebrow'));
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-reading-room::sections.search_hero.heading'));
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $searchAction = (string) ($widget->getMeta('searchAction') ?? '#');
    $searchProvided = (bool) ($widget->getMeta('searchProvided') ?? false);
    $quickLinks = is_array($widget->getMeta('quickLinks')) ? array_slice($widget->getMeta('quickLinks'), 0, 10) : [];
    $variant = (string) ($widget->getMeta('variant') ?? 'full');
@endphp

{{--
    `search-spotlight-hero` — re-scoped from a Cmd+K command palette per the
    programme's feasibility review (§0.4 explicitly bans fuzzy-search command
    palettes/libraries with an unbounded client index): a prominent search
    box plus payload-fed quick links, capped at <= 10 per §0.3.

    Real search integration is provider-resolved: `searchProvided` is set
    by the theme/demo caller when a search package is installed and wires a
    real `searchAction` endpoint; when it is not installed, this view falls
    back to rendering the curated static `quickLinks` only, with the search
    box posting to `searchAction` (default `#`, a same-page no-op) rather
    than shipping any client-side fuzzy-matching JS.

    Two variants:
    - `full` (default): the full landing hero — heading, summary, search
      box, and quick-link grid.
    - `compact`: a single-row search bar plus a smaller quick-link row, for
      placement as a recurring in-page element (e.g. atop a directory) not
      just the homepage.
--}}
<section
    id="search-spotlight-hero"
    class="rr-shell rr-section"
>
    <div class="rr-section-inner">
        <div
            class="rr-search-hero {{ $variant === 'compact' ? 'rr-search-hero-compact' : '' }}"
        >
            @if ($variant !== 'compact')
                <div>
                    <p class="rr-eyebrow">{{ $eyebrow }}</p>
                    <h1>{{ $heading }}</h1>

                    @if ($summary !== '')
                        <p class="rr-lede">{{ $summary }}</p>
                    @endif
                </div>
            @endif

            <form
                action="{{ $searchAction }}"
                method="get"
                class="rr-search-box"
                role="search"
            >
                <label
                    for="rr-search-input"
                    class="sr-only"
                >
                    {{ __('capell-theme-reading-room::sections.search_hero.label') }}
                </label>
                <input
                    id="rr-search-input"
                    type="search"
                    name="q"
                    placeholder="{{ __('capell-theme-reading-room::sections.search_hero.placeholder') }}"
                />
                <kbd class="rr-search-kbd">/</kbd>
            </form>

            @unless ($searchProvided)
                <p class="rr-toc-heading">
                    {{ __('capell-theme-reading-room::sections.search_hero.quick_links_label') }}
                </p>
            @endunless

            <div class="rr-quick-links">
                @foreach ($quickLinks as $link)
                    <a
                        href="{{ data_get($link, 'url', '#') }}"
                        class="rr-quick-link"
                    >
                        <span
                            class="rr-quick-link-tag"
                            >{{ data_get($link, 'tag', '') }}</span
                        >
                        <span>{{ data_get($link, 'label', '') }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
