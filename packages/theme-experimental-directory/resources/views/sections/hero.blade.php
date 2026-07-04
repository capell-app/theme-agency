@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-experimental-directory::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-experimental-directory::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-experimental-directory::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-experimental-directory::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $eyebrow = data_get($section, 'eyebrow', data_get($section, 'kicker', __('capell-theme-experimental-directory::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
@endphp

<section class="exd-section">
    <div class="exd-section-inner exd-hero-grid">
        <div>
            <div class="exd-hero-eyebrow-row">
                <p class="exd-kicker">{{ $eyebrow }}</p>
                <span class="exd-badge">
                    {{ __('capell-theme-experimental-directory::sections.hero.badge') }}
                </span>
            </div>
            <h1>{{ $heading }}</h1>
            <p class="exd-lede">{{ $summary }}</p>
            <div class="exd-actions">
                @foreach ($actions as $action)
                    <a
                        class="exd-button {{ data_get($action, 'style') === 'secondary' ? 'exd-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        <figure class="exd-hero-frame">
            @if (filled($mediaUrl))
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?? $heading }}"
                    width="1200"
                    height="1500"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="exd-media"
                />
            @else
                <div
                    class="exd-media exd-media-empty"
                    aria-hidden="true"
                ></div>
            @endif
        </figure>
    </div>
</section>
