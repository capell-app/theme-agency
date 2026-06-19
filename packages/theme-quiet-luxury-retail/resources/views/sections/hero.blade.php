@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-quiet-luxury-retail::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-quiet-luxury-retail::sections.hero.summary')));
    $primaryLabel = data_get($section, 'primary_label', __('capell-theme-quiet-luxury-retail::sections.hero.primary_label'));
    $primaryUrl = data_get($section, 'primary_url', data_get($section, 'primary.href', '/'));
    $secondaryLabel = data_get($section, 'secondary_label', __('capell-theme-quiet-luxury-retail::sections.hero.secondary_label'));
    $secondaryUrl = data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/'));
    $notes = data_get($section, 'notes', [
        __('capell-theme-quiet-luxury-retail::sections.hero.note_fit'),
        __('capell-theme-quiet-luxury-retail::sections.hero.note_materials'),
        __('capell-theme-quiet-luxury-retail::sections.hero.note_pace'),
    ]);
@endphp

<section class="luxury-section luxury-section-dark">
    <div class="luxury-section-inner luxury-hero-grid">
        <div>
            <p class="luxury-kicker">
                {{ __('capell-theme-quiet-luxury-retail::sections.hero.kicker') }}
            </p>
            <h1>{{ $heading }}</h1>
            <p class="luxury-lede">{{ $summary }}</p>
            <div class="luxury-grid">
                <a
                    class="luxury-button"
                    href="{{ $primaryUrl }}"
                >
                    {{ $primaryLabel }}
                </a>
                <a
                    class="luxury-button luxury-button-secondary"
                    href="{{ $secondaryUrl }}"
                >
                    {{ $secondaryLabel }}
                </a>
            </div>
        </div>

        <aside class="luxury-card">
            <p class="luxury-kicker">
                {{ __('capell-theme-quiet-luxury-retail::sections.hero.panel_kicker') }}
            </p>
            @foreach ($notes as $note)
                <p>{{ $note }}</p>
            @endforeach
        </aside>
    </div>
</section>
