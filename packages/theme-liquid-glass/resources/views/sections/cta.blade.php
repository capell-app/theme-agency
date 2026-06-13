@php
    $actions = $section->actions !== []
        ? $section->actions
        : [
            ['label' => __('capell-theme-liquid-glass::generic.cta_primary_action'), 'url' => '#content', 'style' => 'primary'],
            ['label' => __('capell-theme-liquid-glass::generic.cta_secondary_action'), 'url' => '#proof', 'style' => 'secondary'],
        ];
@endphp

<section class="liquid-glass-section px-5 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="liquid-glass-cta px-6 py-12 text-center sm:px-10 lg:px-16">
            <p class="liquid-glass-eyebrow justify-center">
                {{ __('capell-theme-liquid-glass::generic.cta_eyebrow') }}
            </p>
            <h2
                class="mx-auto mt-4 max-w-3xl text-4xl leading-tight font-black text-balance sm:text-5xl"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary)
                <p
                    class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_74%,transparent)]"
                >
                    {{ $section->summary }}
                </p>
            @endif

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @foreach ($actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        @class([
                            'liquid-glass-button' => ($action['style'] ?? 'primary') === 'primary',
                            'liquid-glass-button-secondary' => ($action['style'] ?? 'primary') !== 'primary',
                        ])
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
