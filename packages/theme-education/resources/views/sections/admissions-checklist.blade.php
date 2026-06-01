@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-education::generic.admissions_checklist_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-admissions-checklist bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-6 md:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#14b8a6] uppercase"
                >
                    {{ __('capell-theme-education::generic.admissions_checklist_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-[#111827]">
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
                    <article class="border border-slate-200 bg-[#f8fbff] p-5">
                        <p class="text-xs font-black text-[#4338ca] uppercase">
                            {{ $item['type'] ?? __('capell-theme-education::generic.enrolment_cta_label') }}
                        </p>
                        <h3 class="mt-2 text-lg font-black text-[#111827]">
                            {{ $item['title'] ?? __('capell-theme-education::generic.enrolment_ready_label') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $item['summary'] ?? __('capell-theme-education::generic.admissions_ready') }}
                        </p>
                    </article>
                @empty
                    <article
                        class="border border-dashed border-slate-300 bg-[#f8fbff] p-6"
                    >
                        <h3 class="text-lg font-black text-[#111827]">
                            {{ __('capell-theme-education::generic.admissions_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
