@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-premium-portfolio-collection::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-premium-portfolio-collection::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-premium-portfolio-collection::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-premium-portfolio-collection::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow', __('capell-theme-premium-portfolio-collection::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));

    $previewVideoUrl = data_get($section, 'preview_video_url', data_get($section, 'media.preview_video_url'));
    $previewGifUrl = data_get($section, 'preview_gif_url', data_get($section, 'media.preview_gif_url'));
    $previewImageUrl = data_get($section, 'preview_image_url', data_get($section, 'media.preview_image_url', $mediaUrl));
    $previewAlt = data_get($section, 'preview_alt', data_get($section, 'media.preview_alt', $mediaAlt ?? __('capell-theme-premium-portfolio-collection::sections.hero.preview_alt')));
    $headerImageUrl = data_get($section, 'header_image_url', data_get($section, 'media.header_image_url'));
    $headerImageAlt = data_get($section, 'header_image_alt', data_get($section, 'media.header_image_alt', __('capell-theme-premium-portfolio-collection::sections.hero.header_image_alt')));
    $notes = data_get($section, 'notes', [
        __('capell-theme-premium-portfolio-collection::sections.hero.note_featured'),
        __('capell-theme-premium-portfolio-collection::sections.hero.note_metadata'),
        __('capell-theme-premium-portfolio-collection::sections.hero.note_newsletter'),
    ]);
@endphp

<section class="ppc-section ppc-section-dark">
    <div class="ppc-section-inner ppc-hero-grid">
        <div>
            <p class="ppc-kicker">{{ $kicker }}</p>
            <h1>{{ $heading }}</h1>
            <p class="ppc-lede">{{ $summary }}</p>
            <div class="ppc-actions">
                @foreach ($actions as $action)
                    <a
                        class="ppc-button {{ data_get($action, 'style') === 'secondary' ? 'ppc-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        <aside class="ppc-plate">
            <p class="ppc-kicker">
                {{ __('capell-theme-premium-portfolio-collection::sections.hero.panel_kicker') }}
            </p>
            <div class="ppc-plate-frame">
                <span class="ppc-badge">
                    {{ __('capell-theme-premium-portfolio-collection::sections.hero.badge') }}
                </span>
                @if ($previewVideoUrl)
                    <video
                        autoplay
                        loop
                        muted
                        playsinline
                        class="ppc-plate-media"
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
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        class="ppc-plate-media"
                    />
                @else
                    <div
                        class="ppc-plate-media ppc-plate-media-empty"
                        aria-hidden="true"
                    ></div>
                @endif
            </div>

            @foreach ($notes as $note)
                <p class="ppc-meta">{{ $note }}</p>
            @endforeach
        </aside>
    </div>

    @if ($headerImageUrl)
        <figure
            class="ppc-section-inner"
            style="padding-top: 0"
        >
            <img
                src="{{ $headerImageUrl }}"
                alt="{{ $headerImageAlt }}"
                loading="eager"
                fetchpriority="high"
                decoding="async"
                class="ppc-plate-frame ppc-plate-frame-header"
            />
        </figure>
    @endif
</section>
