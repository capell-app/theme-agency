@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-local-services::generic.locality_proof_label');
    $summary ??= $section->summary ?? null;
@endphp

<section
    class="theme-section theme-section-locality-proof bg-[#13231f] text-white"
>
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-8 md:grid-cols-[0.78fr_1.22fr]">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#f97316] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.locality_proof_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black">{{ $heading }}</h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-white/75">
                        {{ $summary }}
                    </p>
                @endif
            </div>
            <div class="grid gap-3">
                @forelse ($items as $item)
                    <article class="border border-white/10 bg-white/[0.06] p-5">
                        <p class="text-xs font-black text-[#f97316] uppercase">
                            {{ $item['type'] ?? __('capell-theme-local-services::generic.locality_signal') }}
                        </p>
                        <h3 class="mt-2 text-lg font-black">
                            {{ $item['title'] ?? __('capell-theme-local-services::generic.area_signal') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-white/75">
                            {{ $item['summary'] ?? __('capell-theme-local-services::generic.locality_proof_ready') }}
                        </p>
                    </article>
                @empty
                    <article class="border border-white/10 bg-white/[0.06] p-5">
                        <h3 class="text-lg font-black">
                            {{ __('capell-theme-local-services::generic.locality_proof_ready') }}
                        </h3>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
