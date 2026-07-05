@php
    $heading = data_get($section, 'heading', __('capell-theme-art-paper::sections.gallery.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-art-paper::sections.gallery.summary'));
    $plates = data_get($section, 'items', [
        ['title' => __('capell-theme-art-paper::sections.gallery.sequence_title'), 'summary' => __('capell-theme-art-paper::sections.gallery.sequence_summary')],
        ['title' => __('capell-theme-art-paper::sections.gallery.caption_title'), 'summary' => __('capell-theme-art-paper::sections.gallery.caption_summary')],
    ]);
    $plateShapes = ['dlm-plate-disc', 'dlm-plate-arch', 'dlm-plate-column'];
@endphp

<section
    id="gallery-feature"
    class="dlm-section dlm-section-dark"
>
    <div class="dlm-section-inner dlm-split">
        <div>
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
        </div>
        <div class="dlm-gallery-plates">
            @foreach ($plates as $plate)
                @php
                    $plateImage = data_get($plate, 'image', data_get($plate, 'imageUrl'));
                    $plateAlt = data_get($plate, 'imageAlt', data_get($plate, 'title', ''));
                    $plateShape = $plateShapes[($loop->index) % count($plateShapes)];
                @endphp

                <figure class="dlm-plate">
                    <div class="dlm-plate-frame">
                        @if (filled($plateImage))
                            <img
                                src="{{ $plateImage }}"
                                alt="{{ $plateAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="dlm-plate-media dlm-plate-media-wide"
                            />
                        @else
                            <div
                                class="dlm-plate-media dlm-plate-media-wide dlm-plate-media-empty {{ $plateShape }}"
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
