@php
    $heading = data_get($section, 'heading', __('capell-theme-editorial-serif::sections.cta.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-editorial-serif::sections.cta.summary'));
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();

    if ($actions->isEmpty()) {
        $actions = collect([
            [
                'label' => data_get($section, 'label', __('capell-theme-editorial-serif::sections.cta.primary_label')),
                'url' => data_get($section, 'url', '/'),
                'style' => 'primary',
            ],
        ]);
    }
@endphp

<section
    id="cta"
    class="eser-section eser-cta"
>
    <div class="eser-section-inner">
        <p class="eser-eyebrow">
            {{ __('capell-theme-editorial-serif::sections.cta.eyebrow') }}
        </p>
        <h2>{{ $heading }}</h2>
        <hr class="eser-heading-rule eser-heading-rule-accent" />
        @if (filled($summary))
            <p class="eser-summary">{{ $summary }}</p>
        @endif

        <div class="eser-actions">
            @foreach ($actions as $action)
                <a
                    class="eser-button {{ data_get($action, 'style') === 'secondary' ? 'eser-button-quiet' : '' }}"
                    href="{{ data_get($action, 'url', '/') }}"
                >
                    {{ data_get($action, 'label') }}
                </a>
            @endforeach
        </div>
    </div>
</section>
