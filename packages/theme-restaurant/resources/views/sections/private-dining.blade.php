@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-restaurant::generic.private_dining_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-restaurant::generic.private_dining_summary'));
    $items = $section->items ?? ($items ?? []);

    if (! is_array($items) || $items === []) {
        $items = [
            ['title' => __('capell-theme-restaurant::generic.room_one'), 'summary' => __('capell-theme-restaurant::generic.room_one_summary'), 'metric' => '18'],
            ['title' => __('capell-theme-restaurant::generic.room_two'), 'summary' => __('capell-theme-restaurant::generic.room_two_summary'), 'metric' => '44'],
        ];
    }
@endphp

<section class="theme-section px-6 py-16">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[1fr_1.2fr]">
        <div class="restaurant-frame bg-white p-6">
            <p class="restaurant-eyebrow">
                {{ __('capell-theme-restaurant::generic.private_dining_label') }}
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
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($items as $room)
                <article class="restaurant-room-card">
                    <p
                        class="text-5xl font-black text-[var(--restaurant-copper)]"
                    >
                        {{ $room['metric'] ?? '' }}
                    </p>
                    <h3 class="mt-6 text-2xl font-black">
                        {{ $room['title'] ?? '' }}
                    </h3>
                    @if (($room['summary'] ?? null) !== null)
                        <p
                            class="mt-3 text-sm leading-6 text-[var(--restaurant-muted)]"
                        >
                            {{ $room['summary'] }}
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
