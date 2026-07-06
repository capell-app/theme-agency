{{--
    refraction-grid (Wave 4c signature widget, §D "glassmorphism showcase"):
    a grid whose tiles carry a refraction/distortion visual treatment,
    achieved with `backdrop-filter: blur() saturate()` plus a subtle
    `filter: blur() saturate()` combination on the tile's own background
    layer (see `.refraction-grid-tile` in
    `resources/css/theme-liquid-glass.css`) — no WebGL/canvas, per §D.

    Variant contract (see glass-feature-card.blade.php's docblock for why
    this is inline `variant`-key branching): two reachable states via
    payload `variant`:
    - `even` (default): a uniform grid, every tile the same size.
    - `featured`: the first tile spans two columns/rows, the rest fill in
      around it — same markup and data, a genuinely different layout.
--}}
@php
    $eyebrow = (string) ($widget->getMeta('eyebrow') ?? __('capell-theme-liquid-glass::generic.refraction_grid_eyebrow'));
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-liquid-glass::generic.refraction_grid_heading'));
    $summary = $widget->getMeta('summary') ?? __('capell-theme-liquid-glass::generic.refraction_grid_summary');
    $tiles = is_array($widget->getMeta('tiles')) ? $widget->getMeta('tiles') : [];
    $variant = (string) ($widget->getMeta('variant') ?? 'even');
@endphp

<section class="px-5 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="max-w-3xl">
            @if ($eyebrow !== '')
                <p class="text-xs font-black tracking-[0.08em] text-(--theme-primary) uppercase">
                    {{ $eyebrow }}
                </p>
            @endif

            <h2
                class="mt-4 text-4xl leading-tight font-black text-balance text-(--theme-foreground) sm:text-5xl"
            >
                {{ $heading }}
            </h2>

            @if ($summary)
                <p class="mt-5 text-lg leading-8 text-(--theme-foreground)/75">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div
            @class ([
                'refraction-grid mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3',
                'refraction-grid--featured' => $variant === 'featured',
            ])
            data-variant="{{ $variant }}"
        >
            @foreach ($tiles as $tile)
                <article
                    @class ([
                        'refraction-grid-tile relative overflow-hidden border border-(--theme-foreground)/10',
                        'refraction-grid-tile--lead' => $variant === 'featured' && $loop->first,
                    ])
                >
                    @if (filled(data_get($tile, 'image')))
                        <img
                            src="{{ data_get($tile, 'image') }}"
                            alt="{{ data_get($tile, 'imageAlt', '') }}"
                            loading="lazy"
                            decoding="async"
                            width="480"
                            height="360"
                            class="aspect-[4/3] w-full object-cover"
                        />
                    @endif

                    <div class="refraction-grid-tile-body">
                        <h3
                            class="leading-tight font-black text-(--theme-foreground)"
                        >
                            {{ data_get($tile, 'title', '') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-(--theme-foreground)/70">
                            {{ data_get($tile, 'summary', '') }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
