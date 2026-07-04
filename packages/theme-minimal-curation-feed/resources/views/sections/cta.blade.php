@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section class="mcf-section">
    <div class="mcf-section-inner">
        <p class="mcf-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-minimal-curation-feed::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-minimal-curation-feed::sections.cta.heading')) }}
        </h2>
        <p class="mcf-lede">
            {{ data_get($section, 'summary', __('capell-theme-minimal-curation-feed::sections.cta.summary')) }}
        </p>
        <div class="mcf-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="mcf-button {{ data_get($action, 'style') === 'secondary' ? 'mcf-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="mcf-button"
                    href="{{ data_get($section, 'url', '#newsletter') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-minimal-curation-feed::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
