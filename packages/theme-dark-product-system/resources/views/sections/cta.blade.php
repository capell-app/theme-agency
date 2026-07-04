@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section
    id="cta"
    class="dps-section dps-section-raised"
>
    <div class="dps-section-inner">
        <p class="dps-eyebrow">
            {{ data_get($section, 'kicker', __('capell-theme-dark-product-system::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-dark-product-system::sections.cta.heading')) }}
        </h2>
        <p class="dps-lede">
            {{ data_get($section, 'summary', __('capell-theme-dark-product-system::sections.cta.summary')) }}
        </p>
        <div class="dps-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="dps-button {{ data_get($action, 'style') === 'secondary' ? 'dps-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="dps-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-dark-product-system::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
