@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.featured_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-estate-agents::generic.featured_summary'));
    $items = $section->items ?? ($items ?? []);

    if (! is_array($items) || $items === []) {
        $items = [
            ['title' => __('capell-theme-estate-agents::generic.property_one'), 'summary' => __('capell-theme-estate-agents::generic.property_one_summary'), 'price' => '725,000', 'meta' => '3 bed / Garden / Chain free'],
            ['title' => __('capell-theme-estate-agents::generic.property_two'), 'summary' => __('capell-theme-estate-agents::generic.property_two_summary'), 'price' => '1,150,000', 'meta' => '4 bed / Period / Village edge'],
            ['title' => __('capell-theme-estate-agents::generic.property_three'), 'summary' => __('capell-theme-estate-agents::generic.property_three_summary'), 'price' => '495,000', 'meta' => '2 bed / Balcony / Station quarter'],
        ];
    }
@endphp

<section class="theme-section px-6 py-16">
    <div class="mx-auto max-w-6xl">
        <div class="max-w-3xl">
            <p class="estate-eyebrow">
                {{ __('capell-theme-estate-agents::generic.featured_label') }}
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
        <div class="mt-8 grid gap-4 lg:grid-cols-3">
            @foreach ($items as $property)
                <article class="estate-property-card">
                    <div
                        class="estate-property-image"
                        aria-hidden="true"
                    ></div>
                    <div class="p-5">
                        <p
                            class="text-2xl font-black text-[var(--estate-green)]"
                        >
                            {{ $property['price'] ?? '' }}
                        </p>
                        <h3 class="mt-3 text-xl font-black">
                            {{ $property['title'] ?? '' }}
                        </h3>
                        <p
                            class="mt-2 text-xs font-black text-[var(--estate-muted)] uppercase"
                        >
                            {{ $property['meta'] ?? '' }}
                        </p>
                        @if (($property['summary'] ?? null) !== null)
                            <p
                                class="mt-4 text-sm leading-6 text-[var(--estate-muted)]"
                            >
                                {{ $property['summary'] }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
