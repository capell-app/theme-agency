@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section class="ppc-section ppc-section-dark">
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-agency::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-agency::sections.cta.heading')) }}
        </h2>
        <p class="ppc-lede">
            {{ data_get($section, 'summary', __('capell-theme-agency::sections.cta.summary')) }}
        </p>
        <div class="ppc-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="ppc-button {{ data_get($action, 'style') === 'secondary' ? 'ppc-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="ppc-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-agency::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
