@php
    $items = $section->items ?? $section->steps ?? [];
@endphp

<section class="theme-section theme-section-admissions-funnel bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="max-w-3xl">
            <p class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase">
                {{ __('capell-theme-education::generic.admissions_funnel_label') }}
            </p>
            <h2 class="mt-3 text-4xl font-black text-[#111827]">
                {{ $section->heading ?? __('capell-theme-education::generic.admissions_funnel_heading') }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 text-base leading-8 text-slate-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-4">
            @foreach ($items as $item)
                <article class="rounded-xl border border-[#c7d2fe] bg-[#f8fbff] p-5">
                    <p class="text-xs font-black tracking-widest text-[#4338ca] uppercase">
                        {{ $item['step'] ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </p>
                    <h3 class="mt-2 text-lg font-black text-[#111827]">
                        {{ $item['title'] ?? __('capell-theme-education::generic.admissions_step_label') }}
                    </h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        {{ $item['summary'] ?? __('capell-theme-education::generic.admissions_funnel_ready') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
