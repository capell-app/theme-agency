@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-education::generic.outcomes_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-outcomes bg-[#050b24] text-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-8 md:grid-cols-[0.76fr_1.24fr]">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#5eead4] uppercase"
                >
                    {{ __('capell-theme-education::generic.outcomes_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black">{{ $heading }}</h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-slate-300">
                        {{ $summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-3">
                @forelse ($items as $item)
                    <article class="border border-white/10 bg-white/[0.06] p-5">
                        <p class="text-xs font-black text-[#5eead4] uppercase">
                            {{ $item['metric'] ?? __('capell-theme-education::generic.cohort_metric_label') }}
                        </p>
                        <h3 class="mt-2 text-lg font-black">
                            {{ $item['title'] ?? $item['name'] ?? __('capell-theme-education::generic.course_outcome_signal') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-300">
                            {{ $item['summary'] ?? __('capell-theme-education::generic.outcomes_ready') }}
                        </p>
                    </article>
                @empty
                    <article class="border border-white/10 bg-white/[0.06] p-5">
                        <h3 class="text-lg font-black">
                            {{ __('capell-theme-education::generic.outcomes_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
