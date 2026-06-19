@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.care.fabric_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.care.fabric_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.care.alter_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.care.alter_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.care.store_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.care.store_summary')],
    ]);
@endphp

<section
    class="luxury-section"
    style="background: var(--luxury-field)"
>
    <div class="luxury-section-inner">
        <p class="luxury-kicker">
            {{ __('capell-theme-quiet-luxury-retail::sections.care.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.care.heading')) }}
        </h2>
        <p class="luxury-lede">
            {{ data_get($section, 'summary', __('capell-theme-quiet-luxury-retail::sections.care.summary')) }}
        </p>
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
