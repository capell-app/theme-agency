@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section class="qwg-section qwg-section-dark">
    <div class="qwg-section-inner">
        <p class="qwg-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-quiet-web-gallery::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-web-gallery::sections.cta.heading')) }}
        </h2>
        <p class="qwg-lede">
            {{ data_get($section, 'summary', __('capell-theme-quiet-web-gallery::sections.cta.summary')) }}
        </p>
        <div class="qwg-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="qwg-button {{ data_get($action, 'style') === 'secondary' ? 'qwg-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="qwg-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-quiet-web-gallery::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
