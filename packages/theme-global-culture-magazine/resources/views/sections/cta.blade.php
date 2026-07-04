@php
    $actions = data_get($section, 'actions', []);
    $primaryLabel = data_get($section, 'label', __('capell-theme-global-culture-magazine::sections.cta.button'));
    $primaryUrl = data_get($section, 'url', '/');
@endphp

<section class="gcm-section gcm-section-dark">
    <div class="gcm-section-inner gcm-centered">
        <p class="gcm-kicker">
            {{ __('capell-theme-global-culture-magazine::sections.cta.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-global-culture-magazine::sections.cta.heading')) }}
        </h2>
        <p class="gcm-lede">
            {{ data_get($section, 'summary', __('capell-theme-global-culture-magazine::sections.cta.summary')) }}
        </p>
        <div class="gcm-actions">
            @if (is_iterable($actions) && collect($actions)->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="gcm-button {{ data_get($action, 'style') === 'secondary' ? 'gcm-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', data_get($action, 'href', '/')) }}"
                    >
                        {{ data_get($action, 'label', data_get($action, 'title', '')) }}
                    </a>
                @endforeach
            @else
                <a
                    class="gcm-button"
                    href="{{ $primaryUrl }}"
                >
                    {{ $primaryLabel }}
                </a>
            @endif
        </div>
    </div>
</section>
