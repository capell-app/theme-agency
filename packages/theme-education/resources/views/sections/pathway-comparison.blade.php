@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-education::generic.pathway_comparison_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-pathway-comparison bg-[#f8fbff]">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-education::generic.pathway_comparison_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[#111827]">
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
                <article class="border border-[#c7d2fe] bg-white p-5 shadow-sm">
                    <p class="text-xs font-black text-[#4338ca] uppercase">
                        {{ $item['type'] ?? __('capell-theme-education::generic.pathway_step_label') }}
                    </p>
                    <h3 class="mt-3 text-xl font-black text-[#111827]">
                        {{ $item['title'] ?? __('capell-theme-education::generic.learning_pathways_label') }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $item['summary'] ?? __('capell-theme-education::generic.pathway_ready') }}
                    </p>
                </article>
            @empty
                <article
                    class="border border-dashed border-[#c7d2fe] bg-white p-6"
                >
                    <h3 class="text-lg font-black text-[#111827]">
                        {{ __('capell-theme-education::generic.premium_layout_ready') }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('capell-theme-education::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
