@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-healthcare::generic.care_pathway');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-care-pathway bg-[#f6fbfd]">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-healthcare::generic.care_pathway') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[#14323a]">
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
                <article class="border border-[#d9e8ee] bg-white p-5 shadow-sm">
                    <p class="text-xs font-black text-[#0f766e] uppercase">
                        {{ $item['type'] ?? __('capell-theme-healthcare::generic.service_label') }}
                    </p>
                    <h3 class="mt-3 text-xl font-black text-[#14323a]">
                        {{ $item['title'] ?? __('capell-theme-healthcare::generic.next_available') }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $item['summary'] ?? __('capell-theme-healthcare::generic.care_pathway_ready') }}
                    </p>
                </article>
            @empty
                <article
                    class="border border-dashed border-[#d9e8ee] bg-white p-6"
                >
                    <h3 class="text-lg font-black text-[#14323a]">
                        {{ __('capell-theme-healthcare::generic.care_pathway_ready') }}
                    </h3>
                </article>
            @endforelse
        </div>
    </div>
</section>
