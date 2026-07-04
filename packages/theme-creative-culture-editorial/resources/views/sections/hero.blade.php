@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-creative-culture-editorial::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-creative-culture-editorial::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-creative-culture-editorial::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-creative-culture-editorial::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow', __('capell-theme-creative-culture-editorial::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
@endphp

<section class="cce-section cce-section-dark">
    <div class="cce-section-inner cce-hero-grid">
        <div>
            <p class="cce-kicker">{{ $kicker }}</p>
            <h1>{{ $heading }}</h1>
            <p class="cce-lede">{{ $summary }}</p>
            <div class="cce-actions">
                @foreach ($actions as $action)
                    <a
                        class="cce-button {{ data_get($action, 'style') === 'secondary' ? 'cce-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        <figure>
            @if (filled($mediaUrl))
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?? $heading }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="cce-hero-media"
                />
            @else
                <div
                    class="cce-hero-media cce-media-empty"
                    data-initial="{{ mb_substr(trim(strip_tags((string) $heading)), 0, 1) }}"
                    aria-hidden="true"
                ></div>
            @endif
            <figcaption class="cce-hero-caption">
                {{ $mediaAlt ?? __('capell-theme-creative-culture-editorial::sections.hero.caption') }}
            </figcaption>
        </figure>
    </div>
</section>
