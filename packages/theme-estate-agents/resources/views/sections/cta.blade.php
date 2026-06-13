@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.cta_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-estate-agents::generic.cta_summary'));
    $actions = $section->actions ?? ($actions ?? []);
    $primaryAction = $actions[0] ?? [
        'label' => __('capell-theme-estate-agents::generic.valuation_label'),
        'url' => '#valuation',
    ];
@endphp

<section class="theme-section px-6 py-16">
    <div
        class="estate-cta mx-auto grid max-w-6xl gap-8 p-8 lg:grid-cols-[1fr_auto] lg:items-center"
    >
        <div>
            <p class="estate-eyebrow text-white/70">
                {{ __('capell-theme-estate-agents::generic.cta_label') }}
            </p>
            <h2
                class="mt-4 max-w-3xl text-4xl leading-tight font-black text-white"
            >
                {{ $heading }}
            </h2>
            @if ($summary)
                <p class="mt-5 max-w-2xl text-lg leading-8 text-white/75">
                    {{ $summary }}
                </p>
            @endif
        </div>
        <a
            href="{{ $primaryAction['url'] ?? '#valuation' }}"
            class="estate-button-light"
        >
            {{ $primaryAction['label'] ?? __('capell-theme-estate-agents::generic.valuation_label') }}
        </a>
    </div>
</section>
