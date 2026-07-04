@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section class="cce-section cce-section-dark">
    <div class="cce-section-inner">
        <p class="cce-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-creative-culture-editorial::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-creative-culture-editorial::sections.cta.heading')) }}
        </h2>
        <p class="cce-lede">
            {{ data_get($section, 'summary', __('capell-theme-creative-culture-editorial::sections.cta.summary')) }}
        </p>
        <div class="cce-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="cce-button {{ data_get($action, 'style') === 'secondary' ? 'cce-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="cce-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-creative-culture-editorial::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
