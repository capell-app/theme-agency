@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section class="dlm-section dlm-section-dark">
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-design-led-magazine::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-design-led-magazine::sections.cta.heading')) }}
        </h2>
        <p class="dlm-lede">
            {{ data_get($section, 'summary', __('capell-theme-design-led-magazine::sections.cta.summary')) }}
        </p>
        <div class="dlm-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="dlm-button {{ data_get($action, 'style') === 'secondary' ? 'dlm-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="dlm-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-design-led-magazine::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
