@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-minimal-fashion::sections.care.fabric_title'), 'summary' => __('capell-theme-minimal-fashion::sections.care.fabric_summary')],
        ['title' => __('capell-theme-minimal-fashion::sections.care.alter_title'), 'summary' => __('capell-theme-minimal-fashion::sections.care.alter_summary')],
        ['title' => __('capell-theme-minimal-fashion::sections.care.store_title'), 'summary' => __('capell-theme-minimal-fashion::sections.care.store_summary')],
    ]);
@endphp

<section
    class="fashion-section"
    style="background: var(--fashion-field)"
>
    <div class="fashion-section-inner">
        <p class="fashion-kicker">
            {{ __('capell-theme-minimal-fashion::sections.care.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-minimal-fashion::sections.care.heading')) }}
        </h2>
        <p class="fashion-lede">
            {{ data_get($section, 'summary', __('capell-theme-minimal-fashion::sections.care.summary')) }}
        </p>
        <div class="fashion-grid">
            @foreach ($items as $item)
                <article class="fashion-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
