@php
    /**
     * kinetic-image-sequence (Wave 4a signature widget #1): a bento grid whose
     * layout is driven by each plate's declared aspect ratio, with a scroll-driven
     * "bloom" reveal. Guardrail §0.1: the grid span pattern is NEVER randomised at
     * render — it is a deterministic sequence seeded from a hash of the section's
     * own payload (heading + item titles), so html-cache always serves the same
     * bento arrangement for the same content. Guardrail §0.3: gallery grids are
     * capped at 50 plates; this widget also caps the *bento* pattern rotation to a
     * short deterministic table, decoupled from the payload item count.
     */
    $heading = data_get($section, 'heading', __('capell-theme-art-paper::sections.gallery.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-art-paper::sections.gallery.summary'));
    $plates = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-art-paper::sections.gallery.sequence_title'), 'summary' => __('capell-theme-art-paper::sections.gallery.sequence_summary')],
        ['title' => __('capell-theme-art-paper::sections.gallery.caption_title'), 'summary' => __('capell-theme-art-paper::sections.gallery.caption_summary')],
    ]))->take(50);

    $layoutSeedSource = $heading . '|' . $plates->map(fn (mixed $plate): string => (string) data_get($plate, 'title', ''))->implode('|');
    $layoutSeed = hexdec(substr(md5($layoutSeedSource), 0, 8));

    // Deterministic bento spans: each entry is [column-span, row-span]. The
    // seed only chooses a rotation offset into this fixed table — it never
    // invents new spans, so the grid can never overflow its column count.
    $bentoSpans = [[2, 2], [1, 1], [1, 2], [1, 1], [2, 1], [1, 1]];
    $spanCount = count($bentoSpans);
@endphp

<section
    id="gallery-feature"
    class="dlm-section dlm-section-dark"
    data-widget="kinetic-image-sequence"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-art-paper::sections.gallery.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="dlm-lede">{{ $summary }}</p>
        <div class="dlm-actions">
            <a
                class="dlm-button"
                href="{{ data_get($section, 'url', '/') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-art-paper::sections.gallery.button')) }}
            </a>
        </div>

        <div
            class="dlm-kinetic-grid"
            data-layout-seed="{{ $layoutSeed }}"
        >
            @foreach ($plates as $plate)
                @php
                    $plateImage = data_get($plate, 'image', data_get($plate, 'imageUrl'));
                    $plateAlt = data_get($plate, 'imageAlt', data_get($plate, 'title', ''));
                    $spanIndex = ($layoutSeed + $loop->index) % $spanCount;
                    [$columnSpan, $rowSpan] = $bentoSpans[$spanIndex];
                @endphp

                <figure
                    class="dlm-kinetic-plate"
                    style="--dlm-kinetic-col-span: {{ $columnSpan }}; --dlm-kinetic-row-span: {{ $rowSpan }};"
                >
                    <div class="dlm-plate-frame dlm-kinetic-frame">
                        @if (filled($plateImage))
                            <img
                                src="{{ $plateImage }}"
                                alt="{{ $plateAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="dlm-plate-media dlm-kinetic-media"
                            />
                        @else
                            <div
                                class="dlm-plate-media dlm-kinetic-media dlm-plate-media-empty dlm-plate-arch"
                                aria-hidden="true"
                            ></div>
                        @endif
                    </div>
                    <figcaption class="dlm-plate-caption">
                        <span class="dlm-plate-number">
                            {{ __('capell-theme-art-paper::sections.plate.plate') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span>
                            <strong>
                                {{ data_get($plate, 'title', data_get($plate, 'name', '')) }}.
                            </strong>
                            {{ data_get($plate, 'summary', '') }}
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
