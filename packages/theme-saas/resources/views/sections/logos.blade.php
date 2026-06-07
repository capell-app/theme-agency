@php
    $items = $section->items ?? $section->logos ?? [];
@endphp

<section class="theme-section theme-section-logos bg-white">
    <div class="mx-auto max-w-6xl px-6 py-12">
        @if ($section->heading ?? null)
            <p class="text-center text-xs font-black tracking-[0.18em] text-cyan-700 uppercase">
                {{ $section->heading }}
            </p>
        @endif

        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($items as $item)
                <div class="grid min-h-20 place-items-center rounded-lg border border-slate-200 bg-slate-50 px-4 text-center text-sm font-black text-slate-600">
                    {{ $item['name'] ?? $item['title'] ?? $item['logo'] ?? __('capell-theme-saas::generic.product_signal') }}
                </div>
            @endforeach
        </div>
    </div>
</section>
