{{--
    Signature widget: photo-essay-with-lazy-captions (Part 2 §B, far-field).
    A full-bleed scroll narrative: each frame is a large photograph with a
    caption that reveals via a CSS scroll-driven animation where supported
    (`@supports (animation-timeline: view())`, §0.8 enhancement-only) and is
    simply always-visible everywhere else -- no IntersectionObserver is
    needed because the fallback (a static caption) is already the correct,
    fully accessible result, so no JS ships for this widget at all. Images
    use native `loading="lazy"` (skipping the first frame, which loads
    eagerly since it is normally in the initial viewport). Capped at 8
    frames, well under the §0.3 carousel/grid caps.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-far-field::sections.photo_essay.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-far-field::sections.photo_essay.summary'));
    $frames = collect(data_get($section, 'items', [
        ['image' => '', 'caption' => __('capell-theme-far-field::sections.photo_essay.frame_one'), 'meta' => __('capell-theme-far-field::sections.photo_essay.frame_one_meta')],
        ['image' => '', 'caption' => __('capell-theme-far-field::sections.photo_essay.frame_two'), 'meta' => __('capell-theme-far-field::sections.photo_essay.frame_two_meta')],
        ['image' => '', 'caption' => __('capell-theme-far-field::sections.photo_essay.frame_three'), 'meta' => __('capell-theme-far-field::sections.photo_essay.frame_three_meta')],
    ]))->take(8);
    $variant = (string) data_get($section, 'variant', 'default');
@endphp

<section
    class="gcm-section gcm-section-tinted"
    id="photo-essay"
    data-widget="photo-essay-with-lazy-captions"
    data-variant="{{ $variant }}"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.photo_essay.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="gcm-lede">{{ $summary }}</p>
        </div>

        <div class="gcm-photo-essay">
            @foreach ($frames as $frame)
                @php
                    $frameImage = (string) data_get($frame, 'image', '');
                    $frameCaption = (string) data_get($frame, 'caption', '');
                    $frameMeta = (string) data_get($frame, 'meta', '');
                @endphp

                <figure class="gcm-photo-essay-frame">
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
