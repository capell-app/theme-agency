@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-saas::generic.docs_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-docs-onboarding bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-8 md:grid-cols-[0.78fr_1fr]">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-cyan-700 uppercase"
                >
                    {{ __('capell-theme-saas::generic.docs_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black text-slate-950">
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-slate-600">
                        {{ $summary }}
                    </p>
                @endif
            </div>

            <div
                class="grid gap-3 rounded-xl border border-slate-200 bg-slate-950 p-5 text-white"
            >
                @forelse ($items as $item)
                    <article class="border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-xs font-black text-cyan-200 uppercase">
                            {{ $item['type'] ?? __('capell-theme-saas::generic.activation_stage_label') }}
                        </p>
                        <h3 class="mt-2 text-lg font-black">
                            {{ $item['title'] ?? __('capell-theme-saas::generic.activation_flow_label') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-300">
                            {{ $item['summary'] ?? __('capell-theme-saas::generic.docs_ready') }}
                        </p>
                    </article>
                @empty
                    <article class="border border-white/10 bg-white/[0.04] p-4">
                        <h3 class="text-lg font-black">
                            {{ __('capell-theme-saas::generic.docs_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
