@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-minimal-fashion::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-minimal-fashion::sections.hero.summary')));
    $primaryLabel = data_get($section, 'primary_label', __('capell-theme-minimal-fashion::sections.hero.primary_label'));
    $primaryUrl = data_get($section, 'primary_url', data_get($section, 'primary.href', '/'));
    $secondaryLabel = data_get($section, 'secondary_label', __('capell-theme-minimal-fashion::sections.hero.secondary_label'));
    $secondaryUrl = data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/'));
    $notes = data_get($section, 'notes', [
        __('capell-theme-minimal-fashion::sections.hero.note_fit'),
        __('capell-theme-minimal-fashion::sections.hero.note_materials'),
        __('capell-theme-minimal-fashion::sections.hero.note_pace'),
    ]);
@endphp

<section class="fashion-section fashion-section-dark">
    <div class="fashion-section-inner fashion-hero-grid">
        <div>
            <p class="fashion-kicker">
                {{ __('capell-theme-minimal-fashion::sections.hero.kicker') }}
            </p>
            <h1>{{ $heading }}</h1>
            <p class="fashion-lede">{{ $summary }}</p>
            <div class="fashion-grid">
                <a
                    class="fashion-button"
                    href="{{ $primaryUrl }}"
                >
                    {{ $primaryLabel }}
                </a>
                <a
                    class="fashion-button fashion-button-secondary"
                    href="{{ $secondaryUrl }}"
                >
                    {{ $secondaryLabel }}
                </a>
            </div>
        </div>

        <aside class="fashion-card">
            <p class="fashion-kicker">
                {{ __('capell-theme-minimal-fashion::sections.hero.panel_kicker') }}
            </p>
            @foreach ($notes as $note)
                <p>{{ $note }}</p>
            @endforeach
        </aside>
    </div>
</section>
