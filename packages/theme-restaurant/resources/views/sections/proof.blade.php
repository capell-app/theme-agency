@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-restaurant::generic.proof_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-restaurant::generic.proof_summary'));
    $items = $section->items ?? ($items ?? []);

    if (! is_array($items) || $items === []) {
        $items = [
            ['metric' => '4.8', 'name' => __('capell-theme-restaurant::generic.proof_one'), 'summary' => __('capell-theme-restaurant::generic.proof_one_summary')],
            ['metric' => '36', 'name' => __('capell-theme-restaurant::generic.proof_two'), 'summary' => __('capell-theme-restaurant::generic.proof_two_summary')],
            ['metric' => '11k', 'name' => __('capell-theme-restaurant::generic.proof_three'), 'summary' => __('capell-theme-restaurant::generic.proof_three_summary')],
        ];
    }
@endphp

<section class="theme-section restaurant-proof px-6 py-16">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[0.78fr_1.22fr]">
        <div>
            <p class="restaurant-eyebrow">
                {{ __('capell-theme-restaurant::generic.proof_label') }}
            </p>
            <h2 class="mt-4 text-4xl leading-tight font-black">
                {{ $heading }}
            </h2>
            @if ($summary)
                <p
                    class="mt-5 text-lg leading-8 text-[var(--restaurant-muted)]"
                >
                    {{ $summary }}
                </p>
            @endif
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($items as $proofItem)
                <article class="restaurant-proof-card">
                    <p
                        class="text-4xl font-black text-[var(--restaurant-green)]"
                    >
                        {{ $proofItem['metric'] ?? '' }}
                    </p>
                    <h3 class="mt-4 font-black">
                        {{ $proofItem['name'] ?? $proofItem['title'] ?? '' }}
                    </h3>
                    @if (($proofItem['summary'] ?? null) !== null)
                        <p
                            class="mt-3 text-sm leading-6 text-[var(--restaurant-muted)]"
                        >
                            {{ $proofItem['summary'] }}
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
