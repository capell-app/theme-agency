@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.cta.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-field-guide::sections.cta.summary'));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'label', __('capell-theme-field-guide::sections.cta.primary_label')),
                'url' => data_get($section, 'url', '#newsletter'),
                'style' => 'primary',
            ],
            [
                'label' => __('capell-theme-field-guide::sections.cta.secondary_label'),
                'url' => '#latest-designs',
                'style' => 'secondary',
            ],
        ]);
    }
@endphp

<section
    id="cta"
    class="fga-section fga-section-dark"
>
    <div class="fga-section-inner">
        <div class="fga-section-head-copy">
            <p class="fga-kicker">
                {{ __('capell-theme-field-guide::sections.cta.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="fga-lede">{{ $summary }}</p>
        </div>
        <div class="fga-actions">
            @foreach ($actions as $action)
                <a
                    class="fga-button {{ data_get($action, 'style') === 'secondary' ? 'fga-button-secondary' : '' }}"
                    href="{{ data_get($action, 'url', '/') }}"
                >
                    {{ data_get($action, 'label') }}
                </a>
            @endforeach
        </div>
    </div>
</section>
