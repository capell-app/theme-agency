@php
    $items = $section->items ?? $section->questions ?? [];
@endphp

<section class="theme-section theme-section-faq bg-white">
    <div
        class="mx-auto grid max-w-6xl gap-8 px-6 py-16 lg:grid-cols-[0.72fr_1.28fr]"
    >
        <div>
            <p
                class="text-xs font-black tracking-[0.18em] text-cyan-700 uppercase"
            >
                {{ __('capell-theme-saas::generic.faq_label') }}
            </p>
            <h2 class="mt-3 text-4xl font-black text-slate-950">
                {{ $section->heading ?? __('capell-theme-saas::generic.faq_heading') }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 text-base leading-8 text-slate-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="grid gap-3">
            @foreach ($items as $item)
                <details
                    class="group rounded-xl border border-slate-200 bg-slate-50 p-5"
                >
                    <summary
                        class="cursor-pointer list-none text-lg font-black text-slate-950"
                    >
                        {{ $item['question'] ?? $item['title'] ?? __('capell-theme-saas::generic.faq_question_label') }}
                    </summary>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        {{ $item['answer'] ?? $item['summary'] ?? '' }}
                    </p>
                </details>
            @endforeach
        </div>
    </div>
</section>
