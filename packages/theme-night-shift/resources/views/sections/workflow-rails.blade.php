@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-night-shift::sections.topics.product_title'), 'summary' => __('capell-theme-night-shift::sections.topics.product_summary')],
        ['title' => __('capell-theme-night-shift::sections.topics.design_title'), 'summary' => __('capell-theme-night-shift::sections.topics.design_summary')],
        ['title' => __('capell-theme-night-shift::sections.topics.advice_title'), 'summary' => __('capell-theme-night-shift::sections.topics.advice_summary')],
        ['title' => __('capell-theme-night-shift::sections.topics.culture_title'), 'summary' => __('capell-theme-night-shift::sections.topics.culture_summary')],
    ]);
@endphp

<section
    id="workflow-rails"
    class="dps-section dps-section-raised"
>
    <div class="dps-section-inner">
        <div class="dps-heading-row">
            <div>
                <p class="dps-eyebrow">
                    {{ __('capell-theme-night-shift::sections.topics.kicker') }}
                </p>
                <h2>
                    {{ data_get($section, 'heading', __('capell-theme-night-shift::sections.topics.heading')) }}
                </h2>
            </div>
        </div>

        <div class="dps-rail">
            @foreach ($items as $item)
                <article class="dps-rail-row">
                    <span
                        class="dps-rail-icon"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
