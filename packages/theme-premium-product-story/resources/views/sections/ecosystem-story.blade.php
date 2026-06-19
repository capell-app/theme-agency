@php
    $heading = data_get($section, 'heading', __('capell-theme-premium-product-story::sections.ecosystem.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-premium-product-story::sections.ecosystem.summary'));
    $actions = data_get($section, 'items', [
        ['title' => __('capell-theme-premium-product-story::sections.ecosystem.accessory_title'), 'summary' => __('capell-theme-premium-product-story::sections.ecosystem.accessory_summary')],
        ['title' => __('capell-theme-premium-product-story::sections.ecosystem.service_title'), 'summary' => __('capell-theme-premium-product-story::sections.ecosystem.service_summary')],
    ]);
@endphp

<section class="product-section product-section-dark">
    <div class="product-section-inner product-split">
        <div>
            <p class="product-kicker">
                {{ __('capell-theme-premium-product-story::sections.ecosystem.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="product-lede">{{ $summary }}</p>
            <a
                class="product-button"
                href="{{ data_get($section, 'url', '/') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-premium-product-story::sections.ecosystem.button')) }}
            </a>
        </div>
        <div class="product-grid">
            @foreach ($actions as $action)
                <article class="product-card">
                    <h3>
                        {{ data_get($action, 'title', data_get($action, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($action, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
