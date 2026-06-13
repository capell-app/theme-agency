@php
    $actions = $section->actions !== []
        ? $section->actions
        : [
            ['label' => __('capell-theme-liquid-glass::generic.hero_primary_action'), 'url' => '#content', 'style' => 'primary'],
            ['label' => __('capell-theme-liquid-glass::generic.hero_secondary_action'), 'url' => '#proof', 'style' => 'secondary'],
        ];
    $stats = data_get($section, 'stats', [
        [
            'label' => __('capell-theme-liquid-glass::generic.hero_stat_surfaces_label'),
            'value' => __('capell-theme-liquid-glass::generic.hero_stat_surfaces_value'),
        ],
        [
            'label' => __('capell-theme-liquid-glass::generic.hero_stat_speed_label'),
            'value' => __('capell-theme-liquid-glass::generic.hero_stat_speed_value'),
        ],
        [
            'label' => __('capell-theme-liquid-glass::generic.hero_stat_tokens_label'),
            'value' => __('capell-theme-liquid-glass::generic.hero_stat_tokens_value'),
        ],
    ]);
@endphp

<section class="liquid-glass-hero px-5 py-16 sm:px-6 lg:px-8 lg:py-24">
    <div
        class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:items-center"
    >
        <div>
            <p class="liquid-glass-eyebrow">
                {{ $section->eyebrow ?: __('capell-theme-liquid-glass::generic.hero_default_eyebrow') }}
            </p>

            <h1
                class="mt-6 max-w-4xl text-5xl leading-tight font-black text-balance sm:text-6xl lg:text-7xl"
            >
                {{ $section->heading }}
            </h1>

            @if ($section->summary)
                <p
                    class="mt-6 max-w-2xl text-lg leading-8 text-[color-mix(in_srgb,var(--liquid-glass-foreground)_76%,transparent)] sm:text-xl"
                >
                    {{ $section->summary }}
                </p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
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

            <dl class="mt-10 grid max-w-2xl gap-4 sm:grid-cols-3">
                @foreach ($stats as $stat)
                    <div class="liquid-glass-panel p-4">
                        <dt
                            class="text-xs font-bold tracking-wide text-[color-mix(in_srgb,var(--liquid-glass-foreground)_56%,transparent)] uppercase"
                        >
                            {{ $stat['label'] ?? '' }}
                        </dt>
                        <dd class="mt-2 text-2xl font-black">
                            {{ $stat['value'] ?? '' }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="liquid-glass-hero-media">
            @if ($section->mediaUrl)
                <img
                    src="{{ $section->mediaUrl }}"
                    alt="{{ $section->mediaAlt ?: __('capell-theme-liquid-glass::generic.hero_media_alt') }}"
                    width="1200"
                    height="900"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                    sizes="(min-width: 1024px) 48vw, 100vw"
                    class="h-full min-h-96 w-full rounded-[var(--liquid-glass-radius)] object-cover"
                />
            @else
                <div class="liquid-glass-panel liquid-glass-preview p-5 sm:p-7">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p
                                class="text-sm font-bold tracking-wide text-[var(--theme-primary)] uppercase"
                            >
                                {{ __('capell-theme-liquid-glass::generic.preview_label') }}
                            </p>
                            <p class="mt-2 text-2xl font-black">
                                {{ __('capell-theme-liquid-glass::generic.preview_title') }}
                            </p>
                        </div>
                        <div class="liquid-glass-status">
                            {{ __('capell-theme-liquid-glass::generic.preview_status') }}
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4">
                        @foreach ([
                                      __('capell-theme-liquid-glass::generic.preview_layer_content'),
                                      __('capell-theme-liquid-glass::generic.preview_layer_navigation'),
                                      __('capell-theme-liquid-glass::generic.preview_layer_conversion'),
                                  ] as $layer)
                            <div class="liquid-glass-layer">
                                <span>{{ $layer }}</span>
                                <span>
                                    {{ __('capell-theme-liquid-glass::generic.preview_layer_ready') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 grid grid-cols-3 gap-3">
                        <div class="liquid-glass-tile"></div>
                        <div
                            class="liquid-glass-tile liquid-glass-tile-strong"
                        ></div>
                        <div class="liquid-glass-tile"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
