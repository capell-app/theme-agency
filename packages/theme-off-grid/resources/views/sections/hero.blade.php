@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-off-grid::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-off-grid::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-off-grid::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-off-grid::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow', __('capell-theme-off-grid::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
@endphp

<section class="rwi-section rwi-section-dark">
    <div class="rwi-section-inner rwi-hero-grid">
        <div>
            <p class="rwi-kicker">{{ $kicker }}</p>
            <h1>{{ $heading }}</h1>
            <hr class="rwi-rule" />
            <p class="rwi-lede">{{ $summary }}</p>
            <div class="rwi-actions">
                @foreach ($actions as $action)
                    <a
                        class="rwi-button {{ data_get($action, 'style') === 'secondary' ? 'rwi-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        @if (filled($mediaUrl))
            <figure class="rwi-hero-plate">
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?: $heading }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="rwi-hero-plate-media"
                />
                <figcaption>
                    {{ __('capell-theme-off-grid::sections.plate.fig') }} 01 —
                    {{ $mediaAlt ?? __('capell-theme-off-grid::sections.plate.caption') }}
                </figcaption>
            </figure>
        @endif
    </div>
</section>
