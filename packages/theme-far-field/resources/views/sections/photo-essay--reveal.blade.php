{{--
    Variant: photo-essay-with-lazy-captions / reveal. Same frames, alternating
    left/right caption placement for a more editorial "magazine spread"
    rhythm. Reveal timing is identical CSS-only scroll-driven animation as
    the default variant -- only the layout differs.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-far-field::sections.photo_essay.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-far-field::sections.photo_essay.summary'));
    $frames = collect(data_get($section, 'items', [
        ['image' => '', 'caption' => __('capell-theme-far-field::sections.photo_essay.frame_one'), 'meta' => __('capell-theme-far-field::sections.photo_essay.frame_one_meta')],
        ['image' => '', 'caption' => __('capell-theme-far-field::sections.photo_essay.frame_two'), 'meta' => __('capell-theme-far-field::sections.photo_essay.frame_two_meta')],
        ['image' => '', 'caption' => __('capell-theme-far-field::sections.photo_essay.frame_three'), 'meta' => __('capell-theme-far-field::sections.photo_essay.frame_three_meta')],
    ]))->take(8);
@endphp

<section
    class="gcm-section gcm-section-tinted"
    id="photo-essay"
    data-widget="photo-essay-with-lazy-captions"
    data-variant="reveal"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.photo_essay.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="gcm-lede">{{ $summary }}</p>
        </div>

        <div class="gcm-photo-essay gcm-photo-essay-alternating">
            @foreach ($frames as $frame)
                @php
                    $frameImage = (string) data_get($frame, 'image', '');
                    $frameCaption = (string) data_get($frame, 'caption', '');
                    $frameMeta = (string) data_get($frame, 'meta', '');
                @endphp

                <figure
                    class="gcm-photo-essay-frame {{ $loop->even ? 'gcm-photo-essay-frame-reverse' : '' }}"
                >
                    <div class="gcm-photo-essay-media">
                        @if ($frameImage !== '')
                            <img
                                src="{{ $frameImage }}"
                                alt="{{ $frameCaption }}"
                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                decoding="async"
                            />
                        @else
                            <span
                                class="gcm-plate-initial"
                                aria-hidden="true"
                            >
                                {{ mb_substr(trim($frameMeta) !== '' ? trim($frameMeta) : 'A', 0, 1) }}
                            </span>
                        @endif
                    </div>
                    <figcaption class="gcm-photo-essay-caption">
                        @if ($frameMeta !== '')
                            <span class="gcm-meta">{{ $frameMeta }}</span>
                        @endif
                        <p>{{ $frameCaption }}</p>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
