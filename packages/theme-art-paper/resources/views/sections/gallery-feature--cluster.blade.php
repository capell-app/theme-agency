{{--
    designer-profile-card-cluster (Wave 4a signature widget #2): a cluster of
    designer/contributor profile cards, selected via the `gallery-feature`
    section's `cluster` variant. Payload cap §0.3: profile clusters are a grid
    (≤50), capped generously below that for legibility.
--}}

<section
    id="gallery-feature"
    class="dlm-section dlm-section-dark"
    data-widget="designer-profile-card-cluster"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-art-paper::sections.gallery.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-art-paper::sections.gallery.heading')) }}
        </h2>
        <p class="dlm-lede">{{ data_get($section, 'summary', __('capell-theme-art-paper::sections.gallery.summary')) }}</p>

        <div class="dlm-designer-cluster">
            @foreach (collect(data_get($section, 'items', data_get($section, 'designers', [])))->take(50) as $designer)
                <article class="dlm-designer-card">
                    <div class="dlm-designer-portrait">
                        @if (filled(data_get($designer, 'image', data_get($designer, 'imageUrl'))))
                            <img
                                src="{{ data_get($designer, 'image', data_get($designer, 'imageUrl')) }}"
                                alt="{{ data_get($designer, 'imageAlt', data_get($designer, 'name', data_get($designer, 'title', ''))) }}"
                                loading="lazy"
                                decoding="async"
                            />
                        @else
                            <div
                                class="dlm-plate-media-empty dlm-plate-disc"
                                aria-hidden="true"
                            ></div>
                        @endif
                    </div>
                    <h3>
                        @if (filled(data_get($designer, 'url', data_get($designer, 'href'))))
                            <a
                                class="dlm-title-link"
                                href="{{ data_get($designer, 'url', data_get($designer, 'href')) }}"
                            >
                                {{ data_get($designer, 'name', data_get($designer, 'title', '')) }}
                            </a>
                        @else
                            {{ data_get($designer, 'name', data_get($designer, 'title', '')) }}
                        @endif
                    </h3>
                    <p class="dlm-meta">
                        {{ data_get($designer, 'discipline', data_get($designer, 'meta', '')) }}
                    </p>
                    <p>{{ data_get($designer, 'summary', data_get($designer, 'bio', '')) }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
