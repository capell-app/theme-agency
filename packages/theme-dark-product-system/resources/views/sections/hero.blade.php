@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-dark-product-system::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-dark-product-system::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-dark-product-system::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-dark-product-system::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $eyebrow = data_get($section, 'eyebrow', data_get($section, 'kicker', __('capell-theme-dark-product-system::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
@endphp

<section
    id="hero"
    class="dps-section dps-hero"
>
    <div class="dps-section-inner dps-hero-grid">
        <div>
            <p class="dps-eyebrow">{{ $eyebrow }}</p>
            <h1>{{ $heading }}</h1>
            <p class="dps-lede">{{ $summary }}</p>
            <div class="dps-actions">
                @foreach ($actions as $action)
                    <a
                        class="dps-button {{ data_get($action, 'style') === 'secondary' ? 'dps-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="dps-window">
            <div class="dps-window-chrome">
                <span class="dps-window-dot"></span>
                <span class="dps-window-dot"></span>
                <span class="dps-window-dot"></span>
            </div>

            @if (filled($mediaUrl))
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?? $heading }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="dps-window-media"
                />
            @else
                <div
                    class="dps-window-media dps-window-media-empty"
                    aria-hidden="true"
                ></div>
            @endif
            <p class="dps-window-caption">
                <span>
                    {{ __('capell-theme-dark-product-system::sections.hero.panel_kicker') }}
                </span>
                <span>
                    {{ $mediaAlt ?? __('capell-theme-dark-product-system::sections.hero.panel_caption') }}
                </span>
            </p>
        </div>
    </div>
</section>
