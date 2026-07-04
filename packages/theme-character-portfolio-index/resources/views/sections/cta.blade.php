@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section class="cpi-section cpi-section-dark">
    <div class="cpi-section-inner">
        <p class="cpi-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-character-portfolio-index::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-character-portfolio-index::sections.cta.heading')) }}
        </h2>
        <p class="cpi-lede">
            {{ data_get($section, 'summary', __('capell-theme-character-portfolio-index::sections.cta.summary')) }}
        </p>
        <div class="cpi-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="cpi-button {{ data_get($action, 'style') === 'secondary' ? 'cpi-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="cpi-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-character-portfolio-index::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
