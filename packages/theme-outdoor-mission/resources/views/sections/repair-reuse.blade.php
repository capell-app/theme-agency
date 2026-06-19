@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-outdoor-mission::sections.repair.patch_title'), 'summary' => __('capell-theme-outdoor-mission::sections.repair.patch_summary')],
        ['title' => __('capell-theme-outdoor-mission::sections.repair.trade_title'), 'summary' => __('capell-theme-outdoor-mission::sections.repair.trade_summary')],
        ['title' => __('capell-theme-outdoor-mission::sections.repair.care_title'), 'summary' => __('capell-theme-outdoor-mission::sections.repair.care_summary')],
    ]);
@endphp

<section
    class="outdoor-section"
    style="background: var(--outdoor-field)"
>
    <div class="outdoor-section-inner">
        <p class="outdoor-kicker">
            {{ __('capell-theme-outdoor-mission::sections.repair.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-outdoor-mission::sections.repair.heading')) }}
        </h2>
        <p class="outdoor-lede">
            {{ data_get($section, 'summary', __('capell-theme-outdoor-mission::sections.repair.summary')) }}
        </p>
        <div class="outdoor-grid">
            @foreach ($items as $item)
                <article class="outdoor-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
