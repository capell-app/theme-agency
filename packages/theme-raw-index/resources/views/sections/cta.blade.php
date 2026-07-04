@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section class="rwi-section rwi-section-dark">
    <div class="rwi-section-inner">
        <p class="rwi-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-raw-index::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-raw-index::sections.cta.heading')) }}
        </h2>
        <p class="rwi-lede">
            {{ data_get($section, 'summary', __('capell-theme-raw-index::sections.cta.summary')) }}
        </p>
        <div class="rwi-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="rwi-button {{ data_get($action, 'style') === 'secondary' ? 'rwi-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="rwi-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-raw-index::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
