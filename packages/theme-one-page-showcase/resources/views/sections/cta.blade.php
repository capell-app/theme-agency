@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section class="ops-section ops-section-dark">
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-one-page-showcase::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-one-page-showcase::sections.cta.heading')) }}
        </h2>
        <p class="ops-lede">
            {{ data_get($section, 'summary', __('capell-theme-one-page-showcase::sections.cta.summary')) }}
        </p>
        <div class="ops-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="ops-button {{ data_get($action, 'style') === 'secondary' ? 'ops-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="ops-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-one-page-showcase::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
