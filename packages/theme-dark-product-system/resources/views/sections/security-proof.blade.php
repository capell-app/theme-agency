@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-dark-product-system::sections.authors.design_title'), 'summary' => __('capell-theme-dark-product-system::sections.authors.design_summary')],
        ['title' => __('capell-theme-dark-product-system::sections.authors.advice_title'), 'summary' => __('capell-theme-dark-product-system::sections.authors.advice_summary')],
        ['title' => __('capell-theme-dark-product-system::sections.authors.company_title'), 'summary' => __('capell-theme-dark-product-system::sections.authors.company_summary')],
    ]);
    $badges = [
        __('capell-theme-dark-product-system::sections.authors.badge_soc2'),
        __('capell-theme-dark-product-system::sections.authors.badge_sso'),
        __('capell-theme-dark-product-system::sections.authors.badge_gdpr'),
        __('capell-theme-dark-product-system::sections.authors.badge_uptime'),
    ];
@endphp

<section
    id="security-proof"
    class="dps-section dps-section-field"
>
    <div class="dps-section-inner">
        <p class="dps-eyebrow">
            {{ __('capell-theme-dark-product-system::sections.authors.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-dark-product-system::sections.authors.heading')) }}
        </h2>
        <p class="dps-lede">
            {{ data_get($section, 'summary', __('capell-theme-dark-product-system::sections.authors.summary')) }}
        </p>

        <div class="dps-badge-row">
            @foreach ($badges as $badge)
                <span class="dps-badge">{{ $badge }}</span>
            @endforeach
        </div>

        <div
            class="dps-grid"
            style="margin-top: clamp(2rem, 4vw, 3rem)"
        >
            @foreach ($items as $item)
                <article class="dps-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
