@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-landing-gallery::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-landing-gallery::sections.hero.summary')));
    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow', __('capell-theme-landing-gallery::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));

    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-landing-gallery::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-landing-gallery::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }
@endphp

<section class="lga-section lga-section-dark">
    <div class="lga-section-inner lga-hero-grid">
        <div>
            <p class="lga-eyebrow">{{ $kicker }}</p>
            <h1>{{ $heading }}</h1>
            <p class="lga-lede">{{ $summary }}</p>
            <div class="lga-actions">
                @foreach ($actions as $action)
                    <a
                        class="lga-button {{ data_get($action, 'style') === 'secondary' ? 'lga-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        @if (filled($mediaUrl))
            <img
                src="{{ $mediaUrl }}"
                alt="{{ $mediaAlt ?? $heading }}"
                width="960"
                height="1200"
                loading="eager"
                fetchpriority="high"
                decoding="async"
                class="lga-gallery-media lga-hero-media"
                style="box-shadow: var(--lga-card-shadow)"
            />
        @else
            <aside class="lga-card">
                <p class="lga-eyebrow">
                    {{ __('capell-theme-landing-gallery::sections.hero.panel_kicker') }}
                </p>
                <div class="lga-hero-panel-row">
                    <span>
                        {{ __('capell-theme-landing-gallery::sections.hero.panel_row_one_label') }}
                    </span>
                    <span class="lga-stat-value">
                        {{ __('capell-theme-landing-gallery::sections.hero.panel_row_one_value') }}
                    </span>
                </div>
                <div class="lga-hero-panel-row">
                    <span>
                        {{ __('capell-theme-landing-gallery::sections.hero.panel_row_two_label') }}
                    </span>
                    <span class="lga-stat-value">
                        {{ __('capell-theme-landing-gallery::sections.hero.panel_row_two_value') }}
                    </span>
                </div>
                <div class="lga-hero-panel-row">
                    <span>
                        {{ __('capell-theme-landing-gallery::sections.hero.panel_row_three_label') }}
                    </span>
                    <span class="lga-stat-value">
                        {{ __('capell-theme-landing-gallery::sections.hero.panel_row_three_value') }}
                    </span>
                </div>
            </aside>
        @endif
    </div>
</section>
