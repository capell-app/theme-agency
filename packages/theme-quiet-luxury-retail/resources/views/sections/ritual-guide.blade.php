@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.ritual.cleanse_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.ritual.cleanse_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.ritual.hydrate_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.ritual.hydrate_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.ritual.scent_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.ritual.scent_summary')],
    ]);
@endphp

<section class="luxury-section">
    <div class="luxury-section-inner luxury-split">
        <div>
            <p class="luxury-kicker">
                {{ __('capell-theme-quiet-luxury-retail::sections.ritual.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.ritual.heading')) }}
            </h2>
            <p class="luxury-lede">
                {{ data_get($section, 'summary', __('capell-theme-quiet-luxury-retail::sections.ritual.summary')) }}
            </p>
        </div>
        <div class="luxury-grid">
            @foreach ($items as $item)
                <article class="luxury-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
