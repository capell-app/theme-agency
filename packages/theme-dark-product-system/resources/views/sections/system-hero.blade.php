@php
    $heading = data_get($section, 'heading', __('capell-theme-dark-product-system::sections.featured.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-dark-product-system::sections.featured.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-dark-product-system::sections.featured.product_title'), 'summary' => __('capell-theme-dark-product-system::sections.featured.product_summary')],
        ['title' => __('capell-theme-dark-product-system::sections.featured.design_title'), 'summary' => __('capell-theme-dark-product-system::sections.featured.design_summary')],
        ['title' => __('capell-theme-dark-product-system::sections.featured.advice_title'), 'summary' => __('capell-theme-dark-product-system::sections.featured.advice_summary')],
    ]);
    $mediaUrl = data_get($section, 'image', data_get($section, 'imageUrl'));
    $mediaAlt = data_get($section, 'imageAlt', $heading);
@endphp

<section
    id="system-hero"
    class="dps-section"
>
    <div class="dps-section-inner">
        <p class="dps-eyebrow">
            {{ __('capell-theme-dark-product-system::sections.featured.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="dps-lede">{{ $summary }}</p>

        <div
            class="dps-system-split"
            style="margin-top: clamp(2.5rem, 4vw, 3.5rem)"
        >
            <div class="dps-window">
                <div class="dps-window-chrome">
                    <span class="dps-window-dot"></span>
                    <span class="dps-window-dot"></span>
                    <span class="dps-window-dot"></span>
                </div>

                @if (filled($mediaUrl))
                    <img
                        src="{{ $mediaUrl }}"
                        alt="{{ $mediaAlt }}"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        class="dps-window-media"
                    />
                @else
                    <div
                        class="dps-window-media dps-window-media-empty"
                        aria-hidden="true"
                    ></div>
                @endif
            </div>

            <div style="display: grid; gap: 1rem">
                @foreach ($items as $item)
                    <article class="dps-surface-card">
                        <span class="dps-surface-index">
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
    </div>
</section>
