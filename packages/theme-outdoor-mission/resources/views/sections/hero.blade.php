@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-outdoor-mission::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-outdoor-mission::sections.hero.summary')));
    $primaryLabel = data_get($section, 'primary_label', __('capell-theme-outdoor-mission::sections.hero.primary_label'));
    $primaryUrl = data_get($section, 'primary_url', data_get($section, 'primary.href', '/'));
    $secondaryLabel = data_get($section, 'secondary_label', __('capell-theme-outdoor-mission::sections.hero.secondary_label'));
    $secondaryUrl = data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/'));
    $notes = data_get($section, 'notes', [
        __('capell-theme-outdoor-mission::sections.hero.note_weather'),
        __('capell-theme-outdoor-mission::sections.hero.note_repair'),
        __('capell-theme-outdoor-mission::sections.hero.note_action'),
    ]);
@endphp

<section class="outdoor-section outdoor-section-dark">
    <div class="outdoor-section-inner outdoor-hero-grid">
        <div>
            <p class="outdoor-kicker">
                {{ __('capell-theme-outdoor-mission::sections.hero.kicker') }}
            </p>
            <h1>{{ $heading }}</h1>
            <p class="outdoor-lede">{{ $summary }}</p>
            <div class="outdoor-grid">
                <a
                    class="outdoor-button"
                    href="{{ $primaryUrl }}"
                >
                    {{ $primaryLabel }}
                </a>
                <a
                    class="outdoor-button outdoor-button-secondary"
                    href="{{ $secondaryUrl }}"
                >
                    {{ $secondaryLabel }}
                </a>
            </div>
        </div>

        <aside class="outdoor-card">
            <p class="outdoor-kicker">
                {{ __('capell-theme-outdoor-mission::sections.hero.panel_kicker') }}
            </p>
            @foreach ($notes as $note)
                <p>{{ $note }}</p>
            @endforeach
        </aside>
    </div>
</section>
