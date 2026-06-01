@php
    $proofs = $section->items ?? [
        ['metric' => '420+', 'name' => 'Resources', 'summary' => 'Structured articles, guides, templates, and research notes.'],
        ['metric' => '18', 'name' => 'Topics', 'summary' => 'Curated topic clusters with editorial ownership.'],
        ['metric' => '4.8x', 'name' => 'Discovery', 'summary' => 'Search and cross-link paths keep knowledge moving.'],
    ];
@endphp

<section class="theme-section theme-section-proof bg-[#111827] text-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-6 md:grid-cols-[0.68fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#fbbf24] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.library_evidence_label') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-white">
                    {{ $heading ?? $section->heading }}
                </h2>
            </div>

            @if (($summary ?? $section->summary ?? null) !== null)
                <p
                    class="max-w-2xl text-lg leading-8 text-slate-300 md:justify-self-end"
                >
                    {{ $summary ?? $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-4">
            @foreach ($proofs as $proof)
                <article class="border border-white/15 bg-white/8 p-5">
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#93c5fd] uppercase"
                    >
                        {{ $proof['label'] ?? $proof['name'] ?? __('capell-theme-knowledge::generic.proof_signal') }}
                    </p>
                    <p class="mt-4 text-4xl font-black text-white">
                        {{ $proof['metric'] ?? '' }}
                    </p>
                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        {{ $proof['summary'] ?? $proof['description'] ?? '' }}
                    </p>
                    <span
                        class="mt-5 block h-1 bg-[#f59e0b]"
                        aria-hidden="true"
                    ></span>
                </article>
            @endforeach
        </div>
    </div>
</section>
