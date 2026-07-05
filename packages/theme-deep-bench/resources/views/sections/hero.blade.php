@php
    $eyebrow = data_get($section, 'eyebrow', data_get($section, 'kicker', __('capell-theme-deep-bench::sections.hero.eyebrow')));
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-deep-bench::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-deep-bench::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-deep-bench::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-deep-bench::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
@endphp

<section class="pfd-section">
    <div class="pfd-section-inner pfd-hero-grid">
        <div>
            <p class="pfd-eyebrow">{{ $eyebrow }}</p>
            <h1>{{ $heading }}</h1>
            <p class="pfd-lede">{{ $summary }}</p>
            <div class="pfd-actions">
                @foreach ($actions as $action)
                    <a
                        class="pfd-button {{ data_get($action, 'style') === 'secondary' ? 'pfd-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
            <ul class="pfd-hero-facts">
                <li>
                    <strong>1,200+</strong>
                    {{ __('capell-theme-deep-bench::sections.hero.fact_profiles') }}
                </li>
                <li>
                    <strong>100%</strong>
                    {{ __('capell-theme-deep-bench::sections.hero.fact_reviewed') }}
                </li>
                <li>
                    <strong>
                        <span
                            class="pfd-dot"
                            aria-hidden="true"
                        ></span>
                    </strong>
                    {{ __('capell-theme-deep-bench::sections.hero.fact_updated') }}
                </li>
            </ul>
        </div>

        <figure class="pfd-hero-media">
            @if (filled($mediaUrl))
                <img
                    src="{{ $mediaUrl }}"
                    alt="{{ $mediaAlt ?? $heading }}"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="pfd-photo"
                />
            @else
                <div
                    class="pfd-photo pfd-photo-empty"
                    aria-hidden="true"
                ></div>
            @endif
            <figcaption class="pfd-meta">
                {{ $mediaAlt ?? __('capell-theme-deep-bench::sections.hero.media_caption') }}
            </figcaption>
        </figure>
    </div>
</section>
