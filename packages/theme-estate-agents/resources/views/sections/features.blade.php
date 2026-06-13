@php
    $heading = $section->heading ?? ($heading ?? __('capell-theme-estate-agents::generic.features_heading'));
    $summary = $section->summary ?? ($summary ?? __('capell-theme-estate-agents::generic.features_summary'));
    $features = $section->features ?? ($section->items ?? ($features ?? []));

    if (! is_array($features) || $features === []) {
        $features = [
            ['title' => __('capell-theme-estate-agents::generic.feature_one'), 'description' => __('capell-theme-estate-agents::generic.feature_one_summary')],
            ['title' => __('capell-theme-estate-agents::generic.feature_two'), 'description' => __('capell-theme-estate-agents::generic.feature_two_summary')],
            ['title' => __('capell-theme-estate-agents::generic.feature_three'), 'description' => __('capell-theme-estate-agents::generic.feature_three_summary')],
        ];
    }
@endphp

<section class="theme-section px-6 py-16">
    <div class="mx-auto max-w-6xl">
        <div class="max-w-3xl">
            <p class="estate-eyebrow">
                {{ __('capell-theme-estate-agents::generic.features_label') }}
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
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ($features as $feature)
                <article class="estate-feature-card">
                    <h3 class="text-xl font-black">
                        {{ $feature['title'] ?? '' }}
                    </h3>
                    @if (($feature['description'] ?? $feature['summary'] ?? null) !== null)
                        <p
                            class="mt-4 text-sm leading-6 text-[var(--estate-muted)]"
                        >
                            {{ $feature['description'] ?? $feature['summary'] }}
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
