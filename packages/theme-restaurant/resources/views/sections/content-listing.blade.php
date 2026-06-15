@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-restaurant::generic.listing_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-restaurant::generic.listing_summary'));
    $items = $section->items ?? ($items ?? []);
    $blogAvailable ??= false;
    $publicThemeUrl = 'Capell\\ThemeStudio\\Restaurant\\Support\\PublicThemeUrl';

    if (! is_array($items) || $items === []) {
        $items = [
            ['title' => __('capell-theme-restaurant::generic.listing_one'), 'summary' => __('capell-theme-restaurant::generic.listing_one_summary'), 'type' => __('capell-theme-restaurant::generic.guide_label')],
            ['title' => __('capell-theme-restaurant::generic.listing_two'), 'summary' => __('capell-theme-restaurant::generic.listing_two_summary'), 'type' => __('capell-theme-restaurant::generic.event_label')],
        ];
    }
@endphp

<section class="theme-section px-6 py-16">
    <div class="mx-auto max-w-6xl">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div class="max-w-3xl">
                <p class="restaurant-eyebrow">
                    {{ $blogAvailable ? __('capell-theme-restaurant::generic.blog_connected') : __('capell-theme-restaurant::generic.listing_label') }}
                </p>
                <h2 class="mt-4 text-4xl leading-tight font-black">
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p
                        class="mt-5 text-lg leading-8 text-[var(--restaurant-muted)]"
                    >
                        {{ $summary }}
                    </p>
                @endif
            </div>
        </div>
        <div class="mt-8 grid gap-4 lg:grid-cols-2">
            @foreach ($items as $listingItem)
                <article class="restaurant-listing-card">
                    <p class="restaurant-small-label">
                        {{ $listingItem['type'] ?? __('capell-theme-restaurant::generic.guide_label') }}
                    </p>
                    <h3 class="mt-3 text-2xl font-black">
                        {{ $listingItem['title'] ?? '' }}
                    </h3>
                    @if (($listingItem['summary'] ?? null) !== null)
                        <p
                            class="mt-3 text-sm leading-6 text-[var(--restaurant-muted)]"
                        >
                            {{ $listingItem['summary'] }}
                        </p>
                    @endif

                    @php
                        $listingItemUrl = $publicThemeUrl::link($listingItem['url'] ?? null);
                    @endphp

                    @if ($listingItemUrl !== null)
                        <a
                            href="{{ $listingItemUrl }}"
                            class="restaurant-inline-link mt-5"
                        >
                            {{ __('capell-theme-restaurant::generic.read_more_label') }}
                        </a>
                    @else
                        <p class="restaurant-small-label mt-5">
                            {{ __('capell-theme-restaurant::generic.read_more_unavailable_label') }}
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
