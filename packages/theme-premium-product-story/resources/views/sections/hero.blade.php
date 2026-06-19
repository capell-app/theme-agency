@php
    $heading = data_get($section, 'heading', data_get($section, 'title', __('capell-theme-premium-product-story::sections.hero.heading')));
    $summary = data_get($section, 'summary', data_get($section, 'description', __('capell-theme-premium-product-story::sections.hero.summary')));
    $primaryLabel = data_get($section, 'primary_label', __('capell-theme-premium-product-story::sections.hero.primary_label'));
    $primaryUrl = data_get($section, 'primary_url', data_get($section, 'primary.href', '/'));
    $secondaryLabel = data_get($section, 'secondary_label', __('capell-theme-premium-product-story::sections.hero.secondary_label'));
    $secondaryUrl = data_get($section, 'secondary_url', data_get($section, 'secondary.href', '/'));
    $notes = data_get($section, 'notes', [
        __('capell-theme-premium-product-story::sections.hero.note_families'),
        __('capell-theme-premium-product-story::sections.hero.note_bands'),
        __('capell-theme-premium-product-story::sections.hero.note_purchase'),
    ]);
@endphp

<section class="product-section product-section-dark">
    <div class="product-section-inner product-hero-grid">
        <div>
            <p class="product-kicker">
                {{ __('capell-theme-premium-product-story::sections.hero.kicker') }}
            </p>
            <h1>{{ $heading }}</h1>
            <p class="product-lede">{{ $summary }}</p>
            <div class="product-grid">
                <a
                    class="product-button"
                    href="{{ $primaryUrl }}"
                >
                    {{ $primaryLabel }}
                </a>
                <a
                    class="product-button product-button-secondary"
                    href="{{ $secondaryUrl }}"
                >
                    {{ $secondaryLabel }}
                </a>
            </div>
        </div>

        <aside class="product-card">
            <p class="product-kicker">
                {{ __('capell-theme-premium-product-story::sections.hero.panel_kicker') }}
            </p>
            @foreach ($notes as $note)
                <p>{{ $note }}</p>
            @endforeach
        </aside>
    </div>
</section>
