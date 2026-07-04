@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section class="sbs-section sbs-section-dark">
    <div class="sbs-section-inner">
        <p class="sbs-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-scoreboard-showcase::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-scoreboard-showcase::sections.cta.heading')) }}
        </h2>
        <p class="sbs-lede">
            {{ data_get($section, 'summary', __('capell-theme-scoreboard-showcase::sections.cta.summary')) }}
        </p>
        <div class="sbs-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="sbs-button {{ data_get($action, 'style') === 'secondary' ? 'sbs-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="sbs-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-scoreboard-showcase::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
