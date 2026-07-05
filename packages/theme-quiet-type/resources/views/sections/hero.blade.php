@php
    $eyebrow = data_get($section, 'eyebrow', data_get($section, 'kicker', __('capell-theme-quiet-type::sections.hero.eyebrow')));
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-quiet-type::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-quiet-type::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-quiet-type::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-quiet-type::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
@endphp

<section class="eser-section eser-hero">
    <div class="eser-section-inner">
        <p class="eser-eyebrow">{{ $eyebrow }}</p>
        <h1>{{ $heading }}</h1>
        <hr class="eser-heading-rule eser-heading-rule-accent" />
        <p class="eser-summary">{{ $summary }}</p>

        <div class="eser-actions">
            @foreach ($actions as $action)
                <a
                    class="eser-button {{ data_get($action, 'style') === 'secondary' ? 'eser-button-quiet' : '' }}"
                    href="{{ data_get($action, 'url', '/') }}"
                >
                    {{ data_get($action, 'label') }}
                </a>
            @endforeach
        </div>

        @if (filled($mediaUrl))
            <figure class="eser-hero-figure">
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?? $heading }}"
                    width="1600"
                    height="900"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="eser-hero-media"
                />
                <figcaption class="eser-hero-caption">
                    {{ $mediaAlt ?? __('capell-theme-quiet-type::sections.hero.caption') }}
                </figcaption>
            </figure>
        @endif
    </div>
</section>
