@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.proof_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-estate-agents::generic.proof_summary'));
    $items = $section->items ?? ($items ?? []);

    if (! is_array($items) || $items === []) {
        $items = [
            ['metric' => '4.9', 'name' => __('capell-theme-estate-agents::generic.proof_one'), 'summary' => __('capell-theme-estate-agents::generic.proof_one_summary')],
            ['metric' => '82%', 'name' => __('capell-theme-estate-agents::generic.proof_two'), 'summary' => __('capell-theme-estate-agents::generic.proof_two_summary')],
            ['metric' => '31', 'name' => __('capell-theme-estate-agents::generic.proof_three'), 'summary' => __('capell-theme-estate-agents::generic.proof_three_summary')],
        ];
    }
@endphp

<section class="theme-section px-6 py-16">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[0.75fr_1.25fr]">
        <div>
            <p class="estate-eyebrow">
                {{ __('capell-theme-estate-agents::generic.proof_label') }}
            </p>
            <h2 class="mt-4 text-4xl leading-tight font-black">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-5 text-lg leading-8 text-[var(--estate-muted)]">
                    {{ $summary }}
                </p>
            @endif
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($items as $proofItem)
                <article class="estate-proof-card">
                    <p class="text-4xl font-black text-[var(--estate-green)]">
                        {{ $proofItem['metric'] ?? '' }}
                    </p>
                    <h3 class="mt-4 font-black">
                        {{ $proofItem['name'] ?? $proofItem['title'] ?? '' }}
                    </h3>
                    <p
                        class="mt-3 text-sm leading-6 text-[var(--estate-muted)]"
                    >
                        {{ $proofItem['summary'] ?? '' }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
