@php
    $items = $section->items ?? $section->modules ?? [];
@endphp

<section class="theme-section theme-section-course-detail bg-white">
    <div
        class="mx-auto grid max-w-6xl gap-8 px-6 py-16 lg:grid-cols-[0.72fr_1.28fr]"
    >
        <div>
            <p
                class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
            >
                {{ __('capell-theme-education::generic.course_detail_label') }}
            </p>
            <h2 class="mt-3 text-4xl font-black text-[#111827]">
                {{ $section->heading ?? __('capell-theme-education::generic.course_detail_heading') }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 text-base leading-8 text-slate-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="grid gap-4">
            @foreach ($items as $item)
                <article
                    class="rounded-xl border border-[#c7d2fe] bg-[#f8fbff] p-5"
                >
                    <p
                        class="text-xs font-black tracking-widest text-[#4338ca] uppercase"
                    >
                        {{ $item['meta'] ?? $item['duration'] ?? __('capell-theme-education::generic.course_module_label') }}
                    </p>
                    <h3 class="mt-2 text-xl font-black text-[#111827]">
                        {{ $item['title'] ?? __('capell-theme-education::generic.course_module_label') }}
                    </h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        {{ $item['summary'] ?? $item['description'] ?? __('capell-theme-education::generic.course_detail_ready') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
