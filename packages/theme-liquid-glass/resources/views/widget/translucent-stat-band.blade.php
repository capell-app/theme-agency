{{--
    translucent-stat-band (Wave 4c signature widget, §D "glassmorphism
    showcase"): pairs the shared `count-up-stat` display primitive (Wave 2.7)
    and its `count-up.js` module (Wave 2.6 — the theme opts in by rendering
    the primitive's `data-count-up*` attribute contract; nothing runs unless
    that markup exists) with the glass treatment, so the numeric animation
    reads as counting up through a translucent pane.

    Variant contract (see glass-feature-card.blade.php's docblock for why
    this is inline `variant`-key branching rather than a
    `VariantViewSectionRenderer` sidecar view): two reachable states via
    payload `variant`:
    - `band` (default): a single full-width translucent strip, stats in a row.
    - `panel`: the same stats inside a bordered card, for placement between
      other card-shaped sections rather than as a full-bleed strip.

    Reduced motion is handled entirely by `count-up-stat`/`count-up.js`
    themselves (Wave 2.6/2.7 already ship the `prefers-reduced-motion` guard
    there — see that module's doc block); this view only emits the markup.
--}}
@php
    $eyebrow = (string) ($widget->getMeta('eyebrow') ?? __('capell-theme-liquid-glass::generic.translucent_stat_band_eyebrow'));
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-liquid-glass::generic.translucent_stat_band_heading'));
    $summary = $widget->getMeta('summary') ?? __('capell-theme-liquid-glass::generic.translucent_stat_band_summary');
    $stats = is_array($widget->getMeta('stats')) ? $widget->getMeta('stats') : [];
    $variant = (string) ($widget->getMeta('variant') ?? 'band');
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
                'glass-surface translucent-stat-band mt-10 grid gap-6 sm:grid-cols-3',
                'glass-surface-card border border-(--theme-foreground)/10 bg-(--theme-surface)/80' => $variant === 'panel',
                'bg-(--theme-surface)/70 px-6 py-8' => $variant === 'band',
            ])
            data-variant="{{ $variant }}"
        >
            @foreach ($stats as $stat)
                <x-capell-theme-foundation::display.count-up-stat
                    :value="(float) data_get($stat, 'value', 0)"
                    :label="(string) data_get($stat, 'label', '')"
                    :prefix="(string) data_get($stat, 'prefix', '')"
                    :suffix="(string) data_get($stat, 'suffix', '')"
                    :decimals="(int) data_get($stat, 'decimals', 0)"
                    class="translucent-stat-band-figure"
                />
            @endforeach
        </div>
    </div>
</section>
