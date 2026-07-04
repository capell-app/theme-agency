@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-quiet-web-gallery::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-quiet-web-gallery::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-quiet-web-gallery::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-quiet-web-gallery::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow', __('capell-theme-quiet-web-gallery::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
@endphp

<section class="qwg-section qwg-section-dark">
    <div class="qwg-section-inner qwg-hero-grid">
        <div>
            <p class="qwg-kicker">{{ $kicker }}</p>
            <h1>{{ $heading }}</h1>
            <p class="qwg-lede">{{ $summary }}</p>
            <div class="qwg-actions">
                @foreach ($actions as $action)
                    <a
                        class="qwg-button {{ data_get($action, 'style') === 'secondary' ? 'qwg-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        <figure class="qwg-frame">
            <div class="qwg-frame-mat">
                @if (filled($mediaUrl))
                    <img
                        src="{{ $mediaUrl }}"
                        alt="{{ $mediaAlt ?? $heading }}"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                        class="qwg-frame-media qwg-frame-media-tall"
                    />
                @else
                    <div
                        class="qwg-frame-media qwg-frame-media-tall"
                        aria-hidden="true"
                    ></div>
                @endif
            </div>
            <figcaption class="qwg-frame-caption">
                <span class="qwg-frame-number">
                    {{ __('capell-theme-quiet-web-gallery::sections.frame.figure') }}
                    01
                </span>
                <span>
                    {{ $mediaAlt ?? __('capell-theme-quiet-web-gallery::sections.frame.caption') }}
                </span>
            </figcaption>
        </figure>
    </div>
</section>
