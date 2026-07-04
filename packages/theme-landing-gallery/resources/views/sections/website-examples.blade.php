@php
    $heading = data_get($section, 'heading', __('capell-theme-landing-gallery::sections.website_examples.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-landing-gallery::sections.website_examples.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="website-examples"
    class="lga-section"
>
    <div class="lga-section-inner">
        <p class="lga-eyebrow">
            {{ __('capell-theme-landing-gallery::sections.website_examples.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="lga-lede">{{ $summary }}</p>

        <div class="lga-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $templateUrl = data_get($item, 'templateUrl', '#paid-templates');
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
                        <p class="lga-gallery-meta">
                            {{ data_get($item, 'meta', __('capell-theme-landing-gallery::sections.website_examples.default_meta')) }}
                        </p>
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                        <div class="lga-gallery-actions">
                            @if (filled($itemUrl))
                                <a
                                    class="lga-button lga-button-small lga-button-secondary"
                                    href="{{ $itemUrl }}"
                                >
                                    {{ __('capell-theme-landing-gallery::sections.website_examples.preview_label') }}
                                </a>
                            @endif

                            <a
                                class="lga-button lga-button-small"
                                href="{{ $templateUrl }}"
                            >
                                {{ __('capell-theme-landing-gallery::sections.website_examples.template_label') }}
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
