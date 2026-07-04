@php
    $heading = data_get($section, 'heading', __('capell-theme-landing-gallery::sections.paid_templates.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-landing-gallery::sections.paid_templates.summary'));
    $items = data_get($section, 'items', []);
    $browseLabel = data_get($section, 'label', __('capell-theme-landing-gallery::sections.paid_templates.button'));
    $browseUrl = data_get($section, 'url', '#content-listing');
@endphp

<section
    id="paid-templates"
    class="lga-section lga-section-field"
>
    <div class="lga-section-inner">
        <div
            class="lga-actions"
            style="justify-content: space-between; margin-top: 0"
        >
            <div>
                <p class="lga-eyebrow">
                    {{ __('capell-theme-landing-gallery::sections.paid_templates.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="lga-lede">{{ $summary }}</p>
            </div>
        </div>

        <div class="lga-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $price = data_get($item, 'price');
                    $isFree = data_get($item, 'free', blank($price));
                    $framework = data_get($item, 'framework', __('capell-theme-landing-gallery::sections.paid_templates.default_framework'));
                    $buyUrl = data_get($item, 'url', '#newsletter');
                @endphp

                <article class="lga-gallery-card">
                    @if (filled($itemImage))
                        <img
                            src="{{ $itemImage }}"
                            alt="{{ $itemAlt }}"
                            width="400"
                            height="300"
                            loading="lazy"
                            decoding="async"
                            class="lga-gallery-media"
                        />
                    @else
                        <div
                            class="lga-gallery-media lga-gallery-media-empty"
                            aria-hidden="true"
                        ></div>
                    @endif

                    <div class="lga-gallery-body">
                        <div
                            class="lga-actions"
                            style="
                                margin-top: 0;
                                justify-content: space-between;
                            "
                        >
                            <span class="lga-tag">{{ $framework }}</span>
                            <span
                                class="lga-price-badge {{ $isFree ? 'lga-price-badge-free' : '' }}"
                            >
                                {{ $isFree ? __('capell-theme-landing-gallery::sections.paid_templates.free_label') : $price }}
                            </span>
                        </div>
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>{{ data_get($item, 'summary', '') }}</p>
                        <a
                            class="lga-button lga-button-small"
                            href="{{ $buyUrl }}"
                        >
                            {{ __('capell-theme-landing-gallery::sections.paid_templates.buy_label') }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="lga-actions">
            <a
                class="lga-button lga-button-secondary"
                href="{{ $browseUrl }}"
            >
                {{ $browseLabel }}
            </a>
        </div>
    </div>
</section>
