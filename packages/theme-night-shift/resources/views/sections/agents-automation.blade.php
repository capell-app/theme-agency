@php
    $heading = data_get($section, 'heading', __('capell-theme-night-shift::sections.stories.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-night-shift::sections.stories.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-night-shift::sections.stories.product_title'), 'summary' => __('capell-theme-night-shift::sections.stories.product_summary'), 'meta' => __('capell-theme-night-shift::sections.stories.product_meta')],
        ['title' => __('capell-theme-night-shift::sections.stories.design_title'), 'summary' => __('capell-theme-night-shift::sections.stories.design_summary'), 'meta' => __('capell-theme-night-shift::sections.stories.design_meta')],
        ['title' => __('capell-theme-night-shift::sections.stories.advice_title'), 'summary' => __('capell-theme-night-shift::sections.stories.advice_summary'), 'meta' => __('capell-theme-night-shift::sections.stories.advice_meta')],
    ]);
@endphp

<section
    id="agents-automation"
    class="dps-section"
>
    <div class="dps-section-inner">
        <p class="dps-eyebrow">
            {{ __('capell-theme-night-shift::sections.stories.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="dps-lede">{{ $summary }}</p>

        <div
            class="dps-grid dps-grid-2"
            style="margin-top: clamp(2rem, 4vw, 3rem)"
        >
            @foreach ($items as $item)
                <article class="dps-card">
                    <span
                        class="dps-card-icon"
                        aria-hidden="true"
                    >
                        &#8776;
                    </span>
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <p class="dps-meta">
                        {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-night-shift::sections.stories.default_meta'))) }}
                    </p>
                    <p class="dps-meta">
                        {{ data_get($item, 'care_note', __('capell-theme-night-shift::sections.stories.care_note')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
