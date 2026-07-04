@php
    $heading = data_get($section, 'heading', __('capell-theme-case-study-platform::sections.cta.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-case-study-platform::sections.cta.summary'));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'label', __('capell-theme-case-study-platform::sections.cta.button')),
                'url' => data_get($section, 'url', '/'),
                'style' => 'primary',
            ],
        ]);
    }
@endphp

<section class="csp-section csp-section-dark">
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-case-study-platform::sections.cta.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
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
</section>
