@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-raw-index::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-raw-index::sections.hero.summary')));
    $primaryLabel = data_get($section, 'primary_label', __('capell-theme-raw-index::sections.hero.primary_label'));
    $primaryUrl = data_get($section, 'primary_url', data_get($section, 'primary.href', '/'));
    $secondaryLabel = data_get($section, 'secondary_label', __('capell-theme-raw-index::sections.hero.secondary_label'));
    $secondaryUrl = data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/'));
    $notes = data_get($section, 'notes', [
        __('capell-theme-raw-index::sections.hero.note_featured'),
        __('capell-theme-raw-index::sections.hero.note_metadata'),
        __('capell-theme-raw-index::sections.hero.note_newsletter'),
    ]);
@endphp

<section class="editorial-section editorial-section-dark">
    <div class="editorial-section-inner editorial-hero-grid">
        <div>
            <p class="editorial-kicker">
                {{ __('capell-theme-raw-index::sections.hero.kicker') }}
            </p>
            <h1>{{ $heading }}</h1>
            <p class="editorial-lede">{{ $summary }}</p>
            <div class="editorial-grid">
                <a
                    class="editorial-button"
                    href="{{ $primaryUrl }}"
                >
                    {{ $primaryLabel }}
                </a>
                <a
                    class="editorial-button editorial-button-secondary"
                    href="{{ $secondaryUrl }}"
                >
                    {{ $secondaryLabel }}
                </a>
            </div>
        </div>

        <aside class="editorial-card">
            <p class="editorial-kicker">
                {{ __('capell-theme-raw-index::sections.hero.panel_kicker') }}
            </p>
            @foreach ($notes as $note)
                <p>{{ $note }}</p>
            @endforeach
        </aside>
    </div>
</section>
