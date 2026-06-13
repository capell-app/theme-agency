@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-restaurant::generic.location_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-restaurant::generic.location_summary'));
    $items = $section->items ?? ($items ?? []);

    if (! is_array($items) || $items === []) {
        $items = [
            ['title' => __('capell-theme-restaurant::generic.transport_label'), 'summary' => __('capell-theme-restaurant::generic.transport_summary')],
            ['title' => __('capell-theme-restaurant::generic.parking_label'), 'summary' => __('capell-theme-restaurant::generic.parking_summary')],
            ['title' => __('capell-theme-restaurant::generic.access_label'), 'summary' => __('capell-theme-restaurant::generic.access_summary')],
        ];
    }
@endphp

<section class="theme-section px-6 py-16">
    <div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="restaurant-map-panel p-6">
            <div
                class="grid h-full min-h-80 place-items-center border border-white/20"
            >
                <p class="text-6xl font-black text-white/80">
                    {{ __('capell-theme-restaurant::generic.map_label') }}
                </p>
            </div>
        </div>
        <div class="restaurant-frame bg-white p-6">
            <p class="restaurant-eyebrow">
                {{ __('capell-theme-restaurant::generic.location_label') }}
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

            <div class="mt-7 space-y-4">
                @foreach ($items as $locationNote)
                    <div
                        class="border-t border-[var(--restaurant-border)] pt-4"
                    >
                        <h3 class="font-black">
                            {{ $locationNote['title'] ?? '' }}
                        </h3>
                        <p
                            class="mt-2 text-sm leading-6 text-[var(--restaurant-muted)]"
                        >
                            {{ $locationNote['summary'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
