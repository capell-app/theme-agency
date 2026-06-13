@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.listing_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-estate-agents::generic.listing_summary'));
    $items = $section->items ?? ($items ?? []);

    if (! is_array($items) || $items === []) {
        $items = [
            ['title' => __('capell-theme-estate-agents::generic.listing_one'), 'summary' => __('capell-theme-estate-agents::generic.listing_one_summary'), 'type' => __('capell-theme-estate-agents::generic.guide_label'), 'url' => '#'],
            ['title' => __('capell-theme-estate-agents::generic.listing_two'), 'summary' => __('capell-theme-estate-agents::generic.listing_two_summary'), 'type' => __('capell-theme-estate-agents::generic.report_label'), 'url' => '#'],
        ];
    }
@endphp

<section class="theme-section estate-section-muted px-6 py-16">
    <div class="mx-auto max-w-6xl">
        <div class="max-w-3xl">
            <p class="estate-eyebrow">
                {{ __('capell-theme-estate-agents::generic.listing_label') }}
            </p>
            <h2 class="mt-4 text-4xl leading-tight font-black">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-5 text-lg leading-8 text-[var(--estate-muted)]">
                    {{ $summary }}
                </p>
            @endif
        </div>
        <div class="mt-8 grid gap-4 lg:grid-cols-2">
            @foreach ($items as $listingItem)
                <article class="estate-listing-card">
                    <p class="estate-small-label">
                        {{ $listingItem['type'] ?? __('capell-theme-estate-agents::generic.guide_label') }}
                    </p>
                    <h3 class="mt-3 text-2xl font-black">
                        {{ $listingItem['title'] ?? '' }}
                    </h3>
                    @if (($listingItem['summary'] ?? null) !== null)
                        <p
                            class="mt-3 text-sm leading-6 text-[var(--estate-muted)]"
                        >
                            {{ $listingItem['summary'] }}
                        </p>
                    @endif

                    <a
                        href="{{ $listingItem['url'] ?? '#' }}"
                        class="estate-inline-link mt-5"
                    >
                        {{ __('capell-theme-estate-agents::generic.read_more_label') }}
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
