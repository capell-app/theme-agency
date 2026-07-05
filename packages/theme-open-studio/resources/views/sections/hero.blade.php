@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-open-studio::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-open-studio::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-open-studio::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-open-studio::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow', __('capell-theme-open-studio::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
    $notes = data_get($section, 'notes', [
        __('capell-theme-open-studio::sections.hero.note_featured'),
        __('capell-theme-open-studio::sections.hero.note_metadata'),
        __('capell-theme-open-studio::sections.hero.note_newsletter'),
    ]);
@endphp

<section class="csp-section csp-section-dark">
    <div class="csp-section-inner csp-hero-grid">
        <div>
            <p class="csp-kicker">{{ $kicker }}</p>
            <h1>{{ $heading }}</h1>
            <p class="csp-lede">{{ $summary }}</p>
            <div class="csp-actions">
                @foreach ($actions as $action)
                    <a
                        class="csp-button {{ data_get($action, 'style') === 'secondary' ? 'csp-button-secondary' : '' }}"
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
                    class="csp-cover csp-cover-tall"
                />
            @else
                <div
                    class="csp-cover csp-cover-tall csp-cover-empty"
                    aria-hidden="true"
                ></div>
            @endif
            <figcaption
                class="csp-actions"
                style="
                    margin-top: 1rem;
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 0.5rem;
                "
            >
                <p
                    class="csp-kicker"
                    style="color: var(--csp-accent-bright)"
                >
                    {{ __('capell-theme-open-studio::sections.hero.panel_kicker') }}
                </p>
                @foreach ($notes as $note)
                    <p style="margin: 0; color: var(--csp-night-text)">
                        {{ $note }}
                    </p>
                @endforeach
            </figcaption>
        </figure>
    </div>
</section>
