@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-restaurant::generic.hours_heading'));
    $items = $section->items ?? ($items ?? []);

    if (! is_array($items) || $items === []) {
        $items = [
            ['title' => __('capell-theme-restaurant::generic.lunch_label'), 'summary' => '12:00 - 15:00'],
            ['title' => __('capell-theme-restaurant::generic.dinner_label'), 'summary' => '17:30 - 22:30'],
            ['title' => __('capell-theme-restaurant::generic.bar_label'), 'summary' => '16:00 - late'],
        ];
    }
@endphp

<section class="theme-section px-6 py-16">
    <div
        class="mx-auto max-w-6xl border-y border-[var(--restaurant-border)] py-10"
    >
        <div class="grid gap-8 lg:grid-cols-[0.55fr_1.45fr] lg:items-center">
            <h2 class="text-4xl leading-tight font-black">{{ $heading }}</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($items as $hours)
                    <div>
                        <p class="restaurant-small-label">
                            {{ $hours['title'] ?? '' }}
                        </p>
                        <p class="mt-2 text-xl font-black">
                            {{ $hours['summary'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
