@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-scoreboard-showcase::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-scoreboard-showcase::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-scoreboard-showcase::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-scoreboard-showcase::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $eyebrow = data_get($section, 'eyebrow', data_get($section, 'kicker', __('capell-theme-scoreboard-showcase::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
@endphp

<section class="sbs-section sbs-section-dark">
    <div class="sbs-section-inner sbs-hero-grid">
        <div class="sbs-hero-copy">
            <p class="sbs-kicker">{{ $eyebrow }}</p>
            <h1>{{ $heading }}</h1>
            <p class="sbs-lede">{{ $summary }}</p>
            <div class="sbs-actions">
                @foreach ($actions as $action)
                    <a
                        class="sbs-button {{ data_get($action, 'style') === 'secondary' ? 'sbs-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        @if (filled($mediaUrl))
            <div class="sbs-hero-plate">
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?? $heading }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="sbs-hero-plate-image"
                />
                <span class="sbs-hero-plate-badge">
                    {{ __('capell-theme-scoreboard-showcase::sections.hero.plate_badge') }}
                </span>
            </div>
        @else
            <div
                class="sbs-hero-plate sbs-hero-plate-empty"
                aria-hidden="true"
            >
                <span class="sbs-hero-plate-badge">
                    {{ __('capell-theme-scoreboard-showcase::sections.hero.plate_badge') }}
                </span>
            </div>
        @endif
    </div>
</section>
