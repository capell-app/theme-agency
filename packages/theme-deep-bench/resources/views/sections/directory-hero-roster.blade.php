{{--
    directory-hero-roster — the "roster sorting" mechanic's hero band: a
    directory summary plus count-up role stats built from the shared Wave 2.7
    `count-up-stat` display primitive (theme-foundation
    resources/views/components/display/count-up-stat.blade.php), which wires
    the `data-count-up*` attribute contract for the shared count-up.js module
    (Wave 2.6, already imported by the runtime). This view only emits markup;
    the animation is entirely the shared module's responsibility.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-deep-bench::sections.roster_hero.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-deep-bench::sections.roster_hero.summary'));
    $stats = collect(data_get($section, 'stats', [
        ['value' => 1200, 'label' => __('capell-theme-deep-bench::sections.roster_hero.stat_profiles'), 'suffix' => '+'],
        ['value' => 6, 'label' => __('capell-theme-deep-bench::sections.roster_hero.stat_disciplines')],
        ['value' => 30, 'label' => __('capell-theme-deep-bench::sections.roster_hero.stat_lists')],
        ['value' => 48, 'label' => __('capell-theme-deep-bench::sections.roster_hero.stat_response'), 'suffix' => 'h'],
    ]))->take(8);
@endphp

<section
    id="directory-hero-roster"
    class="pfd-section pfd-section-field"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.roster_hero.eyebrow')) }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="pfd-lede">{{ $summary }}</p>

        @if ($stats->isNotEmpty())
            <div class="pfd-roster-stats">
                @foreach ($stats as $stat)
                    <x-capell-theme-foundation::display.count-up-stat
                        :value="data_get($stat, 'value', 0)"
                        :label="data_get($stat, 'label', '')"
                        :prefix="data_get($stat, 'prefix', '')"
                        :suffix="data_get($stat, 'suffix', '')"
                        :decimals="data_get($stat, 'decimals', 0)"
                        class="pfd-roster-stat"
                    />
                @endforeach
            </div>
        @endif
    </div>
</section>
