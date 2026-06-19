@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-bold-sport-commerce::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-bold-sport-commerce::sections.hero.summary')));
    $primaryLabel = data_get($section, 'primary_label', __('capell-theme-bold-sport-commerce::sections.hero.primary_label'));
    $primaryUrl = data_get($section, 'primary_url', data_get($section, 'primary.href', '/'));
    $secondaryLabel = data_get($section, 'secondary_label', __('capell-theme-bold-sport-commerce::sections.hero.secondary_label'));
    $secondaryUrl = data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/'));
    $notes = data_get($section, 'notes', [
        __('capell-theme-bold-sport-commerce::sections.hero.note_drop'),
        __('capell-theme-bold-sport-commerce::sections.hero.note_categories'),
        __('capell-theme-bold-sport-commerce::sections.hero.note_cards'),
    ]);
@endphp

<section class="sport-section sport-section-dark">
    <div class="sport-section-inner sport-hero-grid">
        <div>
            <p class="sport-kicker">
                {{ __('capell-theme-bold-sport-commerce::sections.hero.kicker') }}
            </p>
            <h1>{{ $heading }}</h1>
            <p class="sport-lede">{{ $summary }}</p>
            <div class="sport-grid">
                <a
                    class="sport-button"
                    href="{{ $primaryUrl }}"
                >
                    {{ $primaryLabel }}
                </a>
                <a
                    class="sport-button sport-button-secondary"
                    href="{{ $secondaryUrl }}"
                >
                    {{ $secondaryLabel }}
                </a>
            </div>
        </div>

        <aside class="sport-card">
            <p class="sport-kicker">
                {{ __('capell-theme-bold-sport-commerce::sections.hero.panel_kicker') }}
            </p>
            @foreach ($notes as $note)
                <p>{{ $note }}</p>
            @endforeach
        </aside>
    </div>
</section>
