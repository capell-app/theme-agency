@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-portfolio::generic.case_study_detail_label');
    $summary ??= $section->summary ?? null;
@endphp

<section
    class="theme-section theme-section-case-study-detail portfolio-bg-deep text-white"
>
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-8 md:grid-cols-[0.78fr_1.22fr]">
            <div>
                <p
                    class="portfolio-text-highlight text-xs font-black tracking-[0.16em] uppercase"
                >
                    {{ __('capell-theme-portfolio::generic.case_study_detail_label') }}
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
                        <p
                            class="portfolio-text-highlight text-xs font-black uppercase"
                        >
                            {{ $item['type'] ?? __('capell-theme-portfolio::generic.outcome_label') }}
                        </p>
                        <h3 class="mt-2 text-lg font-black">
                            {{ $item['title'] ?? __('capell-theme-portfolio::generic.case_file_label') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-300">
                            {{ $item['summary'] ?? __('capell-theme-portfolio::generic.case_study_detail_ready') }}
                        </p>
                    </article>
                @empty
                    <article class="border border-white/10 bg-white/[0.06] p-5">
                        <h3 class="text-lg font-black">
                            {{ __('capell-theme-portfolio::generic.case_study_detail_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
