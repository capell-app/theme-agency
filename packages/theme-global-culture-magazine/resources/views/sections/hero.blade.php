@php
    $eyebrow = data_get($section, 'eyebrow', __('capell-theme-global-culture-magazine::sections.hero.kicker'));
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-global-culture-magazine::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-global-culture-magazine::sections.hero.summary')));
    $rawActions = data_get($section, 'actions', []);
    $actions = collect(is_iterable($rawActions) ? $rawActions : [])
        ->filter(fn (mixed $action): bool => trim((string) data_get($action, 'label', data_get($action, 'title', ''))) !== '')
        ->values();
    $primaryLabel = data_get($section, 'primary_label', __('capell-theme-global-culture-magazine::sections.hero.primary_label'));
    $primaryUrl = data_get($section, 'primary_url', data_get($section, 'primary.href', '/'));
    $secondaryLabel = data_get($section, 'secondary_label', __('capell-theme-global-culture-magazine::sections.hero.secondary_label'));
    $secondaryUrl = data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/'));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'image', ''));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'imageAlt', ''));
    $plateInitial = mb_substr(trim(strip_tags((string) $heading)), 0, 1);
@endphp

<section class="gcm-section gcm-section-dark">
    <div class="gcm-section-inner gcm-hero-grid">
        <div>
            <p class="gcm-kicker">{{ $eyebrow }}</p>
            <h1>{{ $heading }}</h1>
            <div class="gcm-hero-standfirst">
                <p class="gcm-lede">{{ $summary }}</p>
            </div>
            <div class="gcm-actions">
                @if ($actions->isNotEmpty())
                    @foreach ($actions as $action)
                        <a
                            class="gcm-button {{ data_get($action, 'style') === 'secondary' ? 'gcm-button-secondary' : '' }}"
                            href="{{ data_get($action, 'url', data_get($action, 'href', '/')) }}"
                        >
                            {{ data_get($action, 'label', data_get($action, 'title', '')) }}
                        </a>
                    @endforeach
                @else
                    <a
                        class="gcm-button"
                        href="{{ $primaryUrl }}"
                    >
                        {{ $primaryLabel }}
                    </a>
                    <a
                        class="gcm-button gcm-button-secondary"
                        href="{{ $secondaryUrl }}"
                    >
                        {{ $secondaryLabel }}
                    </a>
                @endif
            </div>
        </div>

        <figure class="gcm-plate gcm-plate-tall">
            <div class="gcm-plate-frame">
                @if ($mediaUrl !== '' && $mediaUrl !== null)
                    <img
                        src="{{ $mediaUrl }}"
                        alt="{{ $mediaAlt !== '' ? $mediaAlt : $heading }}"
                        width="960"
                        height="1200"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                    />
                @else
                    <span
                        class="gcm-plate-initial"
                        aria-hidden="true"
                    >
                        {{ $plateInitial }}
                    </span>
                @endif
            </div>
            <figcaption class="gcm-plate-caption">
                <span class="gcm-meta">
                    {{ __('capell-theme-global-culture-magazine::sections.hero.plate_meta') }}
                </span>
                <span>
                    {{ $mediaAlt !== '' ? $mediaAlt : __('capell-theme-global-culture-magazine::sections.hero.plate_caption') }}
                </span>
            </figcaption>
        </figure>
    </div>
</section>
