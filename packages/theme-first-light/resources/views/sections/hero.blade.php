@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-first-light::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-first-light::sections.hero.summary')));
    $eyebrow = data_get($section, 'eyebrow', data_get($section, 'kicker', __('capell-theme-first-light::sections.hero.eyebrow')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-first-light::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '#curation-feed')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-first-light::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '#newsletter')),
                'style' => 'secondary',
            ],
        ]);
    }

    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
@endphp

<section class="mcf-section">
    <div class="mcf-section-inner">
        <p class="mcf-kicker">{{ $eyebrow }}</p>
        <h1>{{ $heading }}</h1>
        <p class="mcf-lede">{{ $summary }}</p>

        <div class="mcf-actions">
            @foreach ($actions as $action)
                <a
                    class="mcf-button {{ data_get($action, 'style') === 'secondary' ? 'mcf-button-secondary' : '' }}"
                    href="{{ data_get($action, 'url', '/') }}"
                >
                    {{ data_get($action, 'label') }}
                </a>
            @endforeach
        </div>

        @if (filled($mediaUrl))
            <figure
                class="mcf-capture"
                style="margin-top: clamp(2.25rem, 5vw, 3.5rem)"
            >
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?? $heading }}"
                    width="1200"
                    height="750"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="mcf-capture-media"
                />
                <figcaption class="mcf-capture-caption">
                    <span>{{ $mediaAlt ?? $heading }}</span>
                    <span class="mcf-meta">
                        {{ __('capell-theme-first-light::sections.hero.capture_meta') }}
                    </span>
                </figcaption>
            </figure>
        @endif
    </div>
</section>
