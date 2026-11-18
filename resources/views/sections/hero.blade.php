@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    $heading = data_get($section, 'heading', data_get($section, 'title'));
    $summary = data_get($section, 'summary', data_get($section, 'description'));
    $actions = collect(data_get($section, 'actions', []))->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label'),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label'),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href')),
                'style' => 'secondary',
            ],
        ]);
    }

    $actions = $actions
        ->map(fn (mixed $action): array => [
            'label' => data_get($action, 'label'),
            'url' => PublicUrlSanitizer::sanitize(data_get($action, 'url', data_get($action, 'href'))),
            'style' => data_get($action, 'style'),
        ])
        ->filter(fn (array $action): bool => filled($action['label']) && filled($action['url']))
        ->values();

    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow'));
    $panelKicker = data_get($section, 'panel_kicker');
    $badge = data_get($section, 'badge');
    $mediaUrl = PublicUrlSanitizer::sanitize(data_get($section, 'mediaUrl', data_get($section, 'media_url')));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));

    $previewVideoUrl = PublicUrlSanitizer::sanitize(data_get($section, 'preview_video_url', data_get($section, 'media.preview_video_url')));
    $previewGifUrl = PublicUrlSanitizer::sanitize(data_get($section, 'preview_gif_url', data_get($section, 'media.preview_gif_url')));
    $previewImageUrl = PublicUrlSanitizer::sanitize(data_get($section, 'preview_image_url', data_get($section, 'media.preview_image_url', $mediaUrl)));
    $previewAlt = (string) data_get($section, 'preview_alt', data_get($section, 'media.preview_alt', $mediaAlt ?? ''));
    $headerImageUrl = PublicUrlSanitizer::sanitize(data_get($section, 'header_image_url', data_get($section, 'media.header_image_url')));
    $headerImageAlt = (string) data_get($section, 'header_image_alt', data_get($section, 'media.header_image_alt', ''));
    $notes = collect(data_get($section, 'notes', []))
        ->filter(fn (mixed $note): bool => is_string($note) && filled($note))
        ->values();
    $hasMedia = filled($previewVideoUrl) || filled($previewGifUrl) || filled($previewImageUrl);
    $hasContent = filled($heading) || filled($summary) || $actions->isNotEmpty() || $hasMedia || filled($headerImageUrl);
@endphp

@if ($hasContent)
    <section class="ppc-section ppc-section-dark">
        <div class="ppc-section-inner ppc-hero-grid">
            <div>
                @if (filled($kicker))
                    <p class="ppc-kicker">{{ $kicker }}</p>
                @endif

                @if (filled($heading))
                    <{{ $headingLevel ?? 'h1' }}
                        >{{ $heading }}</{{ $headingLevel ?? 'h1' }}
                    >
                @endif
                @if (filled($summary))
                    <p class="ppc-lede">{{ $summary }}</p>
                @endif

                @if ($actions->isNotEmpty())
                    <div class="ppc-actions">
                        @foreach ($actions as $action)
                            <a
                                class="ppc-button {{ $action['style'] === 'secondary' ? 'ppc-button-secondary' : '' }}"
                                href="{{ $action['url'] }}"
                            >
                                {{ $action['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($hasMedia || filled($badge) || filled($panelKicker) || $notes->isNotEmpty())
                <aside class="ppc-plate">
                    @if (filled($panelKicker))
                        <p class="ppc-kicker">{{ $panelKicker }}</p>
                    @endif

                    @if ($hasMedia || filled($badge))
                        <div class="ppc-plate-frame">
                            @if (filled($badge))
                                <span class="ppc-badge"> {{ $badge }} </span>
                            @endif

                            @if ($previewVideoUrl)
                                <video
                                    autoplay
                                    loop
                                    muted
                                    playsinline
                                    class="ppc-plate-media"
                                    @if ($previewImageUrl) poster="{{ $previewImageUrl }}" @endif
                                    @if (filled($previewAlt)) aria-label="{{ $previewAlt }}" @endif
                                >
                                    <source
                                        src="{{ $previewVideoUrl }}"
                                        type="video/mp4"
                                    />
                                </video>
                            @elseif ($previewGifUrl || $previewImageUrl)
                                <x-capell::image-source
                                    :image="$previewGifUrl ?: $previewImageUrl"
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
                    @endif

                    @foreach ($notes as $note)
                        <p class="ppc-meta">{{ $note }}</p>
                    @endforeach
                </aside>
            @endif
        </div>

        @if ($headerImageUrl)
            <figure
                class="ppc-section-inner"
                style="padding-top: 0"
            >
                <x-capell::image-source
                    :image="$headerImageUrl"
                    alt="{{ $headerImageAlt }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="ppc-plate-frame ppc-plate-frame-header"
                />
            </figure>
        @endif
    </section>
@endif
