@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-reel-room::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-reel-room::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-reel-room::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-reel-room::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $eyebrow = data_get($section, 'eyebrow', data_get($section, 'kicker', __('capell-theme-reel-room::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt', __('capell-theme-reel-room::sections.hero.still_alt')));
@endphp

<section class="mva-section mva-hero">
    <div class="mva-section-inner mva-hero-grid">
        <div>
            <p class="mva-kicker">{{ $eyebrow }}</p>
            <h1>{{ $heading }}</h1>
            <p class="mva-lede">{{ $summary }}</p>
            <div class="mva-actions">
                @foreach ($actions as $action)
                    <a
                        class="mva-button {{ data_get($action, 'style') === 'secondary' ? 'mva-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mva-hero-still">
            @if (filled($mediaUrl))
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="mva-hero-still-image"
                />
            @endif

            <span
                class="mva-play"
                aria-hidden="true"
            ></span>
            <div class="mva-hero-meta">
                <span>
                    {{ __('capell-theme-reel-room::sections.plate.still') }}
                </span>
                <span>
                    {{ __('capell-theme-reel-room::sections.plate.from_archive') }}
                </span>
            </div>
        </div>
    </div>
</section>
