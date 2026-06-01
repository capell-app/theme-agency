@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-saas::generic.pricing_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-pricing bg-slate-50">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-cyan-700 uppercase"
                >
                    {{ __('capell-theme-saas::generic.pricing_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-slate-950">
                    {{ $heading }}
                </h2>
            </div>
            @if ($summary)
                <p class="text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @forelse ($items as $item)
                <article
                    class="grid min-h-full gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-xs font-black text-cyan-700 uppercase">
                        {{ $item['type'] ?? __('capell-theme-saas::generic.pricing_label') }}
                    </p>
                    <h3 class="text-xl font-black text-slate-950">
                        {{ $item['title'] ?? __('capell-theme-saas::generic.product_signal') }}
                    </h3>
                    <p class="text-sm leading-6 text-slate-600">
                        {{ $item['summary'] ?? __('capell-theme-saas::generic.pricing_ready') }}
                    </p>
                </article>
            @empty
                <article
                    class="border border-dashed border-slate-300 bg-white p-6"
                >
                    <h3 class="text-lg font-black text-slate-950">
                        {{ __('capell-theme-saas::generic.premium_layout_ready') }}
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ __('capell-theme-saas::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
