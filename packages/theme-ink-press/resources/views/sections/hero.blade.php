@php
    $eyebrow = data_get($section, 'eyebrow', data_get($section, 'kicker', __('capell-theme-ink-press::sections.hero.kicker')));
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-ink-press::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-ink-press::sections.hero.summary')));
    $dateline = data_get($section, 'dateline', __('capell-theme-ink-press::sections.hero.dateline'));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(static fn (mixed $action): bool => is_array($action) && data_get($action, 'label') !== null)
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            ['label' => __('capell-theme-ink-press::sections.hero.primary_label'), 'url' => '#', 'style' => 'primary'],
            ['label' => __('capell-theme-ink-press::sections.hero.secondary_label'), 'url' => '#', 'style' => 'secondary'],
        ]);
    }

    $notes = data_get($section, 'notes', [
        __('capell-theme-ink-press::sections.hero.note_lead'),
        __('capell-theme-ink-press::sections.hero.note_photo'),
        __('capell-theme-ink-press::sections.hero.note_commerce'),
    ]);
@endphp

<section class="dnews-section dnews-section-dark">
    <div class="dnews-section-inner dnews-hero-grid">
        <div>
            <p class="dnews-kicker">{{ $eyebrow }}</p>
            <h1>{{ $heading }}</h1>
            <p class="dnews-hero-standfirst">{{ $summary }}</p>
            <p class="dnews-hero-dateline dnews-meta">
                <span>{{ $dateline }}</span>
            </p>
            <div class="dnews-actions">
                @foreach ($actions as $action)
                    <a
                        class="dnews-button {{ data_get($action, 'style') === 'secondary' ? 'dnews-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', data_get($action, 'href', '#')) }}"
                    >
                        {{ data_get($action, 'label', '') }}
                    </a>
                @endforeach
            </div>
        </div>

        <aside
            class="dnews-wire-panel"
            aria-label="{{ __('capell-theme-ink-press::sections.hero.panel_kicker') }}"
        >
            <div class="dnews-wire-panel-head">
                <p class="dnews-meta">
                    {{ __('capell-theme-ink-press::sections.hero.panel_kicker') }}
                </p>
                <p class="dnews-meta dnews-masthead-live">
                    {{ __('capell-theme-ink-press::sections.hero.panel_live') }}
                </p>
            </div>
            <div class="dnews-wire-ticker">
                <div class="dnews-wire-ticker-track">
                    @foreach ($notes as $note)
                        <div class="dnews-wire-item">
                            <span
                                class="dnews-wire-index"
                                aria-hidden="true"
                            >
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <p>{{ $note }}</p>
                        </div>
                    @endforeach
                </div>
                <div
                    class="dnews-wire-ticker-track"
                    aria-hidden="true"
                >
                    @foreach ($notes as $note)
                        <div class="dnews-wire-item">
                            <span
                                class="dnews-wire-index"
                                aria-hidden="true"
                            >
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <p>{{ $note }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</section>
