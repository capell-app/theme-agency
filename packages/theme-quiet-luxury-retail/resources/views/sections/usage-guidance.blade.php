@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-luxury-retail::sections.usage.apply_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.usage.apply_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.usage.pair_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.usage.pair_summary')],
        ['title' => __('capell-theme-quiet-luxury-retail::sections.usage.assist_title'), 'summary' => __('capell-theme-quiet-luxury-retail::sections.usage.assist_summary')],
    ]);
@endphp

<section
    class="luxury-section"
    style="background: var(--luxury-field)"
>
    <div class="luxury-section-inner">
        <p class="luxury-kicker">
            {{ __('capell-theme-quiet-luxury-retail::sections.usage.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-luxury-retail::sections.usage.heading')) }}
        </h2>
        <p class="luxury-lede">
            {{ data_get($section, 'summary', __('capell-theme-quiet-luxury-retail::sections.usage.summary')) }}
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
