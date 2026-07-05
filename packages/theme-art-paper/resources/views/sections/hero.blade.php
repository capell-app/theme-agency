@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-art-paper::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-art-paper::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-art-paper::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-art-paper::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow', __('capell-theme-art-paper::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
@endphp

<section class="dlm-section dlm-section-dark">
    <div class="dlm-section-inner dlm-hero-grid">
        <div>
            <p class="dlm-kicker">{{ $kicker }}</p>
            <h1>{{ $heading }}</h1>
            <hr class="dlm-hero-rule" />
            <p class="dlm-lede">{{ $summary }}</p>
            <div class="dlm-actions">
                @foreach ($actions as $action)
                    <a
                        class="dlm-button {{ data_get($action, 'style') === 'secondary' ? 'dlm-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        <figure class="dlm-plate">
            <div class="dlm-plate-frame">
                @if (filled($mediaUrl))
                    <img
                        src="{{ $mediaUrl }}"
                        alt="{{ $mediaAlt ?? $heading }}"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        class="dlm-plate-media dlm-plate-media-tall"
                    />
                @else
                    <div
                        class="dlm-plate-media dlm-plate-media-tall dlm-plate-media-empty dlm-plate-arch"
                        aria-hidden="true"
                    ></div>
                @endif
            </div>
            <figcaption class="dlm-plate-caption">
                <span class="dlm-plate-number">
                    {{ __('capell-theme-art-paper::sections.plate.figure') }} 01
                </span>
                <span>
                    {{ $mediaAlt ?? __('capell-theme-art-paper::sections.plate.caption') }}
                </span>
            </figcaption>
        </figure>
    </div>
</section>
