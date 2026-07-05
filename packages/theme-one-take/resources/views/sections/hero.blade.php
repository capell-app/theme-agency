@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-one-take::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-one-take::sections.hero.summary')));
    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow', __('capell-theme-one-take::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));

    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-one-take::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-one-take::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }
@endphp

<section class="ops-section ops-section-dark">
    <div class="ops-section-inner ops-hero-grid">
        <div>
            <p class="ops-kicker">{{ $kicker }}</p>
            <h1>{{ $heading }}</h1>
            <p class="ops-lede">{{ $summary }}</p>
            <div class="ops-actions">
                @foreach ($actions as $action)
                    <a
                        class="ops-button {{ data_get($action, 'style') === 'secondary' ? 'ops-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        <figure class="ops-capture-frame">
            <div
                class="ops-capture-chrome"
                aria-hidden="true"
            >
                <span></span>
                <span></span>
                <span></span>
            </div>

            @if (filled($mediaUrl))
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?? $heading }}"
                    width="900"
                    height="1400"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="ops-capture-media ops-capture-media-tall"
                />
            @else
                <div
                    class="ops-capture-media ops-capture-media-tall ops-capture-media-empty"
                    aria-hidden="true"
                ></div>
            @endif
        </figure>
    </div>
</section>
