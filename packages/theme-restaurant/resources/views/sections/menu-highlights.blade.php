@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-restaurant::generic.menu_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-restaurant::generic.menu_summary'));
    $items = $section->items ?? ($items ?? []);

    if (! is_array($items) || $items === []) {
        $items = [
            ['title' => __('capell-theme-restaurant::generic.menu_item_one'), 'summary' => __('capell-theme-restaurant::generic.menu_item_one_summary'), 'price' => '18'],
            ['title' => __('capell-theme-restaurant::generic.menu_item_two'), 'summary' => __('capell-theme-restaurant::generic.menu_item_two_summary'), 'price' => '24'],
            ['title' => __('capell-theme-restaurant::generic.menu_item_three'), 'summary' => __('capell-theme-restaurant::generic.menu_item_three_summary'), 'price' => '16'],
        ];
    }
@endphp

<section
    id="menu"
    class="theme-section px-6 py-16"
>
    <div class="mx-auto max-w-6xl">
        <div class="grid gap-8 lg:grid-cols-[0.72fr_1.28fr]">
            <div>
                <p class="restaurant-eyebrow">
                    {{ __('capell-theme-restaurant::generic.menu_label') }}
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
            <div class="grid gap-4">
                @foreach ($items as $menuItem)
                    <article class="restaurant-menu-row">
                        <div>
                            <h3 class="text-xl font-black">
                                {{ $menuItem['title'] ?? '' }}
                            </h3>
                            @if (($menuItem['summary'] ?? null) !== null)
                                <p
                                    class="mt-2 text-sm leading-6 text-[var(--restaurant-muted)]"
                                >
                                    {{ $menuItem['summary'] }}
                                </p>
                            @endif
                        </div>
                        @if (($menuItem['price'] ?? null) !== null)
                            <p
                                class="text-2xl font-black text-[var(--restaurant-green)]"
                            >
                                {{ $menuItem['price'] }}
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
