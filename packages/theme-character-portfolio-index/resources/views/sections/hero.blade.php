@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-character-portfolio-index::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-character-portfolio-index::sections.hero.summary')));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'primary_label', __('capell-theme-character-portfolio-index::sections.hero.primary_label')),
                'url' => data_get($section, 'primary_url', data_get($section, 'primary.href', '/')),
                'style' => 'primary',
            ],
            [
                'label' => data_get($section, 'secondary_label', __('capell-theme-character-portfolio-index::sections.hero.secondary_label')),
                'url' => data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/')),
                'style' => 'secondary',
            ],
        ]);
    }

    $kicker = data_get($section, 'kicker', data_get($section, 'eyebrow', __('capell-theme-character-portfolio-index::sections.hero.kicker')));
    $mediaUrl = data_get($section, 'mediaUrl', data_get($section, 'media_url'));
    $mediaAlt = data_get($section, 'mediaAlt', data_get($section, 'media_alt'));
    $notes = data_get($section, 'notes', [
        __('capell-theme-character-portfolio-index::sections.hero.note_featured'),
        __('capell-theme-character-portfolio-index::sections.hero.note_metadata'),
        __('capell-theme-character-portfolio-index::sections.hero.note_newsletter'),
    ]);
@endphp

<section class="cpi-section cpi-section-dark">
    <div class="cpi-section-inner cpi-hero-grid">
        <div>
            <p class="cpi-kicker">{{ $kicker }}</p>
            <h1>{{ $heading }}</h1>
            <p class="cpi-lede">{{ $summary }}</p>
            <div class="cpi-actions">
                @foreach ($actions as $action)
                    <a
                        class="cpi-button {{ data_get($action, 'style') === 'secondary' ? 'cpi-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            </div>
        </div>

        <div>
            @if (filled($mediaUrl))
                <figure class="cpi-plate">
                    <div class="cpi-plate-frame">
                        <img
                            src="{{ $mediaUrl }}"
                            alt="{{ $mediaAlt ?? $heading }}"
                            loading="eager"
                            fetchpriority="high"
                            decoding="async"
                            class="cpi-plate-media"
                        />
                    </div>
                    <figcaption class="cpi-plate-caption">
                        <span class="cpi-plate-number">
                            {{ __('capell-theme-character-portfolio-index::sections.plate.figure') }}
                        </span>
                        <span>
                            {{ $mediaAlt ?? __('capell-theme-character-portfolio-index::sections.plate.caption') }}
                        </span>
                    </figcaption>
                </figure>
            @endif

            <aside
                class="cpi-card"
                style="margin-top: 1.5rem"
            >
                <p class="cpi-kicker">
                    {{ __('capell-theme-character-portfolio-index::sections.hero.panel_kicker') }}
                </p>
                @foreach ($notes as $note)
                    <p>{{ $note }}</p>
                @endforeach
            </aside>
        </div>
    </div>
</section>
