@php
    $brandName = $section->brandName ?? ($brandName ?? __('capell-theme-restaurant::generic.brand_name'));
    $items = $section->items ?? ($items ?? []);
    $reservationUrl = $section->reservationUrl ?? ($reservationUrl ?? '#reservations');
@endphp

<nav
    class="restaurant-nav border-b border-[var(--restaurant-border)] bg-white px-6 py-4"
>
    <div
        class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4"
    >
        <a
            href="/"
            class="restaurant-text-ink text-lg font-black"
        >
            {{ $brandName }}
        </a>

        <div class="flex flex-wrap items-center gap-5 text-sm font-bold">
            @foreach ($items as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="restaurant-link"
                >
                    {{ $item['label'] ?? $item['title'] ?? '' }}
                </a>
            @endforeach
        </div>

        <a
            href="{{ $reservationUrl }}"
            class="restaurant-button-primary"
        >
            {{ __('capell-theme-restaurant::generic.reserve_label') }}
        </a>
    </div>
</nav>
