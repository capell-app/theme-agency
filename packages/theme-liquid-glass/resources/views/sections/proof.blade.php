@php
    $items = $section->items !== []
        ? $section->items
        : [
            [
                'metric' => __('capell-theme-liquid-glass::generic.proof_metric_launches'),
                'quote' => __('capell-theme-liquid-glass::generic.proof_quote_launches'),
                'name' => __('capell-theme-liquid-glass::generic.proof_name_launches'),
                'role' => __('capell-theme-liquid-glass::generic.proof_role_launches'),
            ],
            [
                'metric' => __('capell-theme-liquid-glass::generic.proof_metric_surfaces'),
                'quote' => __('capell-theme-liquid-glass::generic.proof_quote_surfaces'),
                'name' => __('capell-theme-liquid-glass::generic.proof_name_surfaces'),
                'role' => __('capell-theme-liquid-glass::generic.proof_role_surfaces'),
            ],
            [
                'metric' => __('capell-theme-liquid-glass::generic.proof_metric_tokens'),
                'quote' => __('capell-theme-liquid-glass::generic.proof_quote_tokens'),
                'name' => __('capell-theme-liquid-glass::generic.proof_name_tokens'),
                'role' => __('capell-theme-liquid-glass::generic.proof_role_tokens'),
            ],
        ];
@endphp

<section
    id="proof"
    class="liquid-glass-section px-5 py-16 sm:px-6 lg:px-8"
>
    <div class="mx-auto max-w-7xl">
        <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
            <div>
                <p class="liquid-glass-eyebrow">
                    {{ __('capell-theme-liquid-glass::generic.proof_eyebrow') }}
                </p>
                <h2
                    class="mt-4 text-4xl leading-tight font-black text-balance sm:text-5xl"
                >
                    {{ $section->heading }}
                </h2>
            </div>
            @if ($section->summary)
                <p
                    class="text-lg leading-8 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_72%,transparent)]"
                >
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-5 lg:grid-cols-3">
            @foreach ($items as $item)
                <figure class="liquid-glass-panel p-6">
                    @if (! empty($item['image']))
                        <img
                            src="{{ $item['image'] }}"
                            alt="{{ $item['name'] ?? $item['logo'] ?? __('capell-theme-liquid-glass::generic.proof_image_alt') }}"
                            width="720"
                            height="480"
                            loading="lazy"
                            decoding="async"
                            class="mb-6 aspect-[3/2] w-full rounded-[calc(var(--liquid-glass-radius)-0.25rem)] object-cover"
                        />
                    @endif

                    <p class="text-3xl font-black text-[var(--theme-primary)]">
                        {{ $item['metric'] ?? $item['logo'] ?? __('capell-theme-liquid-glass::generic.proof_metric_fallback') }}
                    </p>
                    @if (! empty($item['quote']))
                        <blockquote
                            class="mt-5 text-lg leading-8 font-semibold"
                        >
                            "{{ $item['quote'] }}"
                        </blockquote>
                    @endif

                    <figcaption
                        class="mt-6 text-sm leading-6 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_64%,transparent)]"
                    >
                        <span
                            class="font-bold text-[var(--liquid-glass-foreground)]"
                        >
                            {{ $item['name'] ?? __('capell-theme-liquid-glass::generic.proof_name_fallback') }}
                        </span>
                        @if (! empty($item['role']))
                            <span class="block">{{ $item['role'] }}</span>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
