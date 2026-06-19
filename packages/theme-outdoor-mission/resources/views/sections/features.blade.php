@php
    $heading = data_get($section, 'heading', __('capell-theme-outdoor-mission::sections.features.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-outdoor-mission::sections.features.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-outdoor-mission::sections.features.shell_title'), 'summary' => __('capell-theme-outdoor-mission::sections.features.shell_summary'), 'meta' => __('capell-theme-outdoor-mission::sections.features.shell_meta')],
        ['title' => __('capell-theme-outdoor-mission::sections.features.pack_title'), 'summary' => __('capell-theme-outdoor-mission::sections.features.pack_summary'), 'meta' => __('capell-theme-outdoor-mission::sections.features.pack_meta')],
        ['title' => __('capell-theme-outdoor-mission::sections.features.fleece_title'), 'summary' => __('capell-theme-outdoor-mission::sections.features.fleece_summary'), 'meta' => __('capell-theme-outdoor-mission::sections.features.fleece_meta')],
    ]);
@endphp

<section class="outdoor-section">
    <div class="outdoor-section-inner">
        <p class="outdoor-kicker">
            {{ __('capell-theme-outdoor-mission::sections.features.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="outdoor-lede">{{ $summary }}</p>

        <div class="outdoor-grid">
            @foreach ($items as $item)
                <article class="outdoor-card outdoor-product-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <div class="outdoor-product-specs">
                        <span>
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-outdoor-mission::sections.features.default_meta'))) }}
                        </span>
                        <span>
                            {{ data_get($item, 'repair_note', __('capell-theme-outdoor-mission::sections.features.repair_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
