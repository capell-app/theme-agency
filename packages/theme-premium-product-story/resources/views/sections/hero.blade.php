@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-premium-product-story::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-premium-product-story::sections.hero.summary')));
    $primaryLabel = data_get($section, 'primary_label', __('capell-theme-premium-product-story::sections.hero.primary_label'));
    $primaryUrl = data_get($section, 'primary_url', data_get($section, 'primary.href', '/'));
    $secondaryLabel = data_get($section, 'secondary_label', __('capell-theme-premium-product-story::sections.hero.secondary_label'));
    $secondaryUrl = data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/'));
    $previewVideoUrl = data_get($section, 'preview_video_url', data_get($section, 'media.preview_video_url'));
    $previewGifUrl = data_get($section, 'preview_gif_url', data_get($section, 'media.preview_gif_url'));
    $previewImageUrl = data_get($section, 'preview_image_url', data_get($section, 'media.preview_image_url'));
    $previewAlt = data_get($section, 'preview_alt', data_get($section, 'media.preview_alt', __('capell-theme-premium-product-story::sections.hero.preview_alt')));
    $headerImageUrl = data_get($section, 'header_image_url', data_get($section, 'media.header_image_url'));
    $headerImageAlt = data_get($section, 'header_image_alt', data_get($section, 'media.header_image_alt', __('capell-theme-premium-product-story::sections.hero.header_image_alt')));
    $notes = data_get($section, 'notes', [
        __('capell-theme-premium-product-story::sections.hero.note_families'),
        __('capell-theme-premium-product-story::sections.hero.note_bands'),
        __('capell-theme-premium-product-story::sections.hero.note_purchase'),
    ]);
@endphp

<section class="product-section product-section-dark">
    <div class="product-section-inner product-hero-grid">
        <div>
            <p class="product-kicker">
                {{ __('capell-theme-premium-product-story::sections.hero.kicker') }}
            </p>
            <h1>{{ $heading }}</h1>
            <p class="product-lede">{{ $summary }}</p>
            <div class="product-grid">
                <a
                    class="product-button"
                    href="{{ $primaryUrl }}"
                >
                    {{ $primaryLabel }}
                </a>
                <a
                    class="product-button product-button-secondary"
                    href="{{ $secondaryUrl }}"
                >
                    {{ $secondaryLabel }}
                </a>
            </div>
        </div>

        <aside class="product-card">
            <p class="product-kicker">
                {{ __('capell-theme-premium-product-story::sections.hero.panel_kicker') }}
            </p>
            @if ($previewVideoUrl || $previewGifUrl || $previewImageUrl)
                <figure class="product-hero-preview">
                    @if ($previewVideoUrl)
                        <video
                            autoplay
                            loop
                            muted
                            playsinline
                            @if ($previewImageUrl) poster="{{ $previewImageUrl }}" @endif
                            aria-label="{{ $previewAlt }}"
                        >
                            <source
                                src="{{ $previewVideoUrl }}"
                                type="video/mp4"
                            />
                        </video>
                    @elseif ($previewGifUrl || $previewImageUrl)
                        <img
                            src="{{ $previewGifUrl ?: $previewImageUrl }}"
                            alt="{{ $previewAlt }}"
                        />
                    @endif
                </figure>
            @endif

            @foreach ($notes as $note)
                <p>{{ $note }}</p>
            @endforeach
        </aside>
    </div>

    @if ($headerImageUrl)
        <figure class="product-hero-header-image">
            <img
                src="{{ $headerImageUrl }}"
                alt="{{ $headerImageAlt }}"
            />
        </figure>
    @endif
</section>
