@php
    $brandName = $section->brandName ?? ($brandName ?? __('capell-theme-estate-agents::generic.brand_name'));
    $items = $section->items ?? ($items ?? []);
    $valuationUrl = $section->valuationUrl ?? ($valuationUrl ?? '#valuation');
@endphp

<nav
    class="estate-nav border-b border-[var(--estate-border)] bg-white px-6 py-4"
>
    <div
        class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4"
    >
        <a
            href="/"
            class="estate-text-ink text-lg font-black"
        >
            {{ $brandName }}
        </a>

        <div class="flex flex-wrap items-center gap-5 text-sm font-bold">
            @foreach ($items as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="estate-link"
                >
                    {{ $item['label'] ?? $item['title'] ?? '' }}
                </a>
            @endforeach
        </div>

        <a
            href="{{ $valuationUrl }}"
            class="estate-button-primary"
        >
            {{ __('capell-theme-estate-agents::generic.valuation_label') }}
        </a>
    </div>
</nav>
