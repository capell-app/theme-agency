@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-nonprofit::generic.volunteer_shifts_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-volunteer-shifts bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-6 md:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#166534] uppercase"
                >
                    {{ __('capell-theme-nonprofit::generic.volunteer_shifts_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[#132016]">
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        {{ $summary }}
                    </p>
                @endif
            </div>
            <div class="grid gap-3">
                @forelse ($items as $item)
                    <article class="border border-slate-200 bg-[#f7fbf5] p-5">
                        <p class="text-xs font-black text-[#eab308] uppercase">
                            {{ $item['type'] ?? __('capell-theme-nonprofit::generic.supporter_cta_label') }}
                        </p>
                        <h3 class="mt-2 text-lg font-black text-[#132016]">
                            {{ $item['title'] ?? __('capell-theme-nonprofit::generic.volunteer_shifts_label') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $item['summary'] ?? __('capell-theme-nonprofit::generic.volunteer_shifts_ready') }}
                        </p>
                    </article>
                @empty
                    <article
                        class="border border-dashed border-slate-300 bg-[#f7fbf5] p-6"
                    >
                        <h3 class="text-lg font-black text-[#132016]">
                            {{ __('capell-theme-nonprofit::generic.volunteer_shifts_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
