@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.local_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-estate-agents::generic.local_summary'));
    $items = $section->items ?? ($items ?? []);
    $addressAvailable ??= false;

    if (! is_array($items) || $items === []) {
        $items = [
            ['title' => __('capell-theme-estate-agents::generic.schools_label'), 'summary' => __('capell-theme-estate-agents::generic.schools_summary')],
            ['title' => __('capell-theme-estate-agents::generic.commute_label'), 'summary' => __('capell-theme-estate-agents::generic.commute_summary')],
            ['title' => __('capell-theme-estate-agents::generic.market_label'), 'summary' => __('capell-theme-estate-agents::generic.market_summary')],
        ];
    }
@endphp

<section class="theme-section estate-section-muted px-6 py-16">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[0.86fr_1.14fr]">
        <div>
            <p class="estate-eyebrow">
                {{ $addressAvailable ? __('capell-theme-estate-agents::generic.address_connected') : __('capell-theme-estate-agents::generic.local_label') }}
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
        <div class="grid gap-4">
            @foreach ($items as $localItem)
                <article class="estate-guide-row">
                    <h3 class="text-xl font-black">
                        {{ $localItem['title'] ?? '' }}
                    </h3>
                    <p class="text-sm leading-6 text-[var(--estate-muted)]">
                        {{ $localItem['summary'] ?? '' }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
