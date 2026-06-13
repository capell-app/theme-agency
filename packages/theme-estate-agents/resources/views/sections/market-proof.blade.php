@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.market_proof_heading'));
    $items = $section->items ?? ($items ?? []);

    if (! is_array($items) || $items === []) {
        $items = [
            ['metric' => '98%', 'name' => __('capell-theme-estate-agents::generic.market_proof_one'), 'summary' => __('capell-theme-estate-agents::generic.market_proof_one_summary')],
            ['metric' => '21', 'name' => __('capell-theme-estate-agents::generic.market_proof_two'), 'summary' => __('capell-theme-estate-agents::generic.market_proof_two_summary')],
            ['metric' => '14d', 'name' => __('capell-theme-estate-agents::generic.market_proof_three'), 'summary' => __('capell-theme-estate-agents::generic.market_proof_three_summary')],
        ];
    }
@endphp

<section class="theme-section estate-proof-band px-6 py-16">
    <div class="mx-auto max-w-6xl">
        <p class="estate-eyebrow text-white/70">
            {{ __('capell-theme-estate-agents::generic.market_proof_label') }}
        </p>
        <h2 class="mt-4 max-w-3xl text-4xl leading-tight font-black text-white">
            {{ $heading }}
        </h2>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ($items as $proofItem)
                <article class="estate-dark-card">
                    <p class="text-4xl font-black text-[var(--estate-lime)]">
                        {{ $proofItem['metric'] ?? '' }}
                    </p>
                    <h3 class="mt-4 text-xl font-black text-white">
                        {{ $proofItem['name'] ?? $proofItem['title'] ?? '' }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-white/70">
                        {{ $proofItem['summary'] ?? '' }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
