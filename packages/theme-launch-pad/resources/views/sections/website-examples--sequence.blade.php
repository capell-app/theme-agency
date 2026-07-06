@php
    // website-examples-grid (Wave 4c signature widget), stagger-launch-sequence
    // variant: cards ride a momentum scroll-snap rail (scroll-snap-type: x
    // mandatory / scroll-snap-align: start -- §0.4, no JS scroll-hijacking
    // library) and stagger into view on a deterministic nth-child delay as
    // the rail scrolls into the viewport. Payload cap ≤50 items (§0.3).
    $heading = data_get($section, 'heading', __('capell-theme-launch-pad::sections.website_examples.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-launch-pad::sections.website_examples.summary'));
    $items = collect(data_get($section, 'items', []))->take(50)->values();
@endphp

<section
    id="website-examples"
    class="lga-section"
>
    <div class="lga-section-inner">
        <p class="lga-eyebrow">
            {{ __('capell-theme-launch-pad::sections.website_examples.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="lga-lede">{{ $summary }}</p>

        <div
            class="lga-grid lga-grid-rail"
            role="list"
        >
            @foreach ($items as $index => $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $templateUrl = data_get($item, 'templateUrl', '#paid-templates');
                    $step = $index % 6;
                @endphp

                <article
                    class="lga-gallery-card lga-rail-card lga-sequence-step"
                    style="--lga-step: {{ $step }}"
                    role="listitem"
                >
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
                            {{ data_get($item, 'meta', __('capell-theme-launch-pad::sections.website_examples.default_meta')) }}
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
                                    {{ __('capell-theme-launch-pad::sections.website_examples.preview_label') }}
                                </a>
                            @endif

                            <a
                                class="lga-button lga-button-small"
                                href="{{ $templateUrl }}"
                            >
                                {{ __('capell-theme-launch-pad::sections.website_examples.template_label') }}
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
