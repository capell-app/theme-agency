@php
    $heading ??= $section->heading ?? null;
    $impactItems = $section->items ?? [
        [
            'metric' => '12k',
            'summary' => __('capell-theme-nonprofit::generic.impact_metric_people'),
        ],
        [
            'metric' => '84%',
            'summary' => __('capell-theme-nonprofit::generic.impact_metric_progress'),
        ],
        [
            'metric' => '31',
            'summary' => __('capell-theme-nonprofit::generic.impact_metric_partners'),
        ],
    ];
@endphp

<section
    class="theme-section theme-section-impact nonprofit-bg-primary-deep text-white"
>
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <p
                class="nonprofit-text-accent text-xs font-black tracking-[0.18em] uppercase"
            >
                {{ __('capell-theme-nonprofit::generic.impact_label') }}
            </p>
            <h2 class="mt-4 max-w-2xl text-4xl font-black tracking-tight">
                {{ $heading }}
            </h2>
        @endisset

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($impactItems as $item)
                <article class="border border-white/15 bg-white/10 p-6">
                    <p class="nonprofit-text-accent text-4xl font-black">
                        {{ $item['metric'] }}
                    </p>
                    <p
                        class="nonprofit-text-on-dark-muted mt-3 text-sm leading-6 font-bold"
                    >
                        {{ $item['summary'] ?? $item['label'] ?? __('capell-theme-nonprofit::generic.impact_label') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
