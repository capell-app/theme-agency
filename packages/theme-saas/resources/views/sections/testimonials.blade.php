@php
    $items = $section->items ?? $section->testimonials ?? [];
@endphp

<section class="theme-section theme-section-testimonials bg-slate-50">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="max-w-3xl">
            <p
                class="text-xs font-black tracking-[0.18em] text-cyan-700 uppercase"
            >
                {{ __('capell-theme-saas::generic.customer_proof_label') }}
            </p>
            <h2 class="mt-3 text-4xl font-black text-slate-950">
                {{ $section->heading ?? __('capell-theme-saas::generic.customer_proof_heading') }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 text-base leading-8 text-slate-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ($items as $item)
                <article
                    class="grid min-h-full gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-3xl font-black text-slate-950">
                        {{ $item['metric'] ?? $item['result'] ?? '' }}
                    </p>
                    <blockquote class="text-sm leading-7 text-slate-600">
                        {{ $item['quote'] ?? $item['summary'] ?? '' }}
                    </blockquote>
                    <p class="mt-auto text-sm font-black text-slate-950">
                        {{ $item['name'] ?? __('capell-theme-saas::generic.customer_label') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
