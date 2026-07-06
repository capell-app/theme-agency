{{--
    directory-hero-roster--compact — same count-up-stat roster band, laid out
    as a tighter single-row strip for pages that already carry a full hero
    above it (e.g. the homepage, which places `hero` first).
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
    class="pfd-section pfd-section-field pfd-roster-compact"
>
    <div class="pfd-section-inner">
        <div class="pfd-roster-compact-head">
            <p class="pfd-eyebrow">
                {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.roster_hero.eyebrow')) }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="pfd-lede">{{ $summary }}</p>
        </div>

        @if ($stats->isNotEmpty())
            <div class="pfd-roster-stats pfd-roster-stats-row">
                @foreach ($stats as $stat)
                    <x-capell-theme-foundation::display.count-up-stat
                        :value="data_get($stat, 'value', 0)"
                        :label="data_get($stat, 'label', '')"
                        :prefix="data_get($stat, 'prefix', '')"
                        :suffix="data_get($stat, 'suffix', '')"
                        :decimals="data_get($stat, 'decimals', 0)"
                        class="pfd-roster-stat pfd-roster-stat-row"
                    />
                @endforeach
            </div>
        @endif
    </div>
</section>
