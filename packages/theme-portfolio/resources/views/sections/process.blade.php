@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-portfolio::generic.process_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-process bg-[#f8fafc]">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#7c2d12] uppercase"
                >
                    {{ __('capell-theme-portfolio::generic.process_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[#0f172a]">
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
                <article class="border border-slate-200 bg-white p-5">
                    <p class="text-xs font-black text-[#9a3412] uppercase">
                        {{ $item['type'] ?? __('capell-theme-portfolio::generic.capability_signal') }}
                    </p>
                    <h3 class="mt-2 text-xl font-black text-[#0f172a]">
                        {{ $item['title'] ?? __('capell-theme-portfolio::generic.project_label') }}
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ $item['summary'] ?? __('capell-theme-portfolio::generic.process_ready') }}
                    </p>
                </article>
            @empty
                <article
                    class="border border-dashed border-slate-300 bg-white p-6"
                >
                    <h3 class="text-lg font-black text-[#0f172a]">
                        {{ __('capell-theme-portfolio::generic.premium_layout_ready') }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('capell-theme-portfolio::generic.premium_layout_empty') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
