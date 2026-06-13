@php
    $features = $section->features !== []
        ? $section->features
        : [
            [
                'title' => __('capell-theme-liquid-glass::generic.feature_surface_title'),
                'description' => __('capell-theme-liquid-glass::generic.feature_surface_summary'),
                'type' => __('capell-theme-liquid-glass::generic.feature_surface_type'),
            ],
            [
                'title' => __('capell-theme-liquid-glass::generic.feature_rhythm_title'),
                'description' => __('capell-theme-liquid-glass::generic.feature_rhythm_summary'),
                'type' => __('capell-theme-liquid-glass::generic.feature_rhythm_type'),
            ],
            [
                'title' => __('capell-theme-liquid-glass::generic.feature_tokens_title'),
                'description' => __('capell-theme-liquid-glass::generic.feature_tokens_summary'),
                'type' => __('capell-theme-liquid-glass::generic.feature_tokens_type'),
            ],
        ];
@endphp

<section
    id="content"
    class="liquid-glass-section px-5 py-16 sm:px-6 lg:px-8"
>
    <div class="mx-auto max-w-7xl">
        <div class="max-w-3xl">
            <p class="liquid-glass-eyebrow">
                {{ __('capell-theme-liquid-glass::generic.features_eyebrow') }}
            </p>
            <h2
                class="mt-4 text-4xl leading-tight font-black text-balance sm:text-5xl"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary)
                <p
                    class="mt-5 text-lg leading-8 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_72%,transparent)]"
                >
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-3">
            @foreach ($features as $feature)
                <article class="liquid-glass-panel p-6">
                    <p class="text-sm font-bold text-[var(--theme-primary)]">
                        {{ $feature['type'] ?? __('capell-theme-liquid-glass::generic.feature_default_type') }}
                    </p>
                    <h3 class="mt-5 text-2xl leading-tight font-black">
                        {{ $feature['title'] }}
                    </h3>
                    <p
                        class="mt-4 text-sm leading-7 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_70%,transparent)]"
                    >
                        {{ $feature['description'] }}
                    </p>

                    @if (! empty($feature['image']))
                        <img
                            src="{{ $feature['image'] }}"
                            alt=""
                            width="720"
                            height="520"
                            loading="lazy"
                            decoding="async"
                            class="mt-6 aspect-[4/3] w-full rounded-[calc(var(--liquid-glass-radius)-0.25rem)] object-cover"
                        />
                    @else
                        <div class="mt-6 grid gap-2">
                            <span class="liquid-glass-rule w-3/4"></span>
                            <span class="liquid-glass-rule w-11/12"></span>
                            <span class="liquid-glass-rule w-2/3"></span>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
