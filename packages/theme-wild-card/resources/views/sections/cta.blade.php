@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section class="exd-section exd-section-paper">
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ data_get($section, 'kicker', __('capell-theme-wild-card::sections.cta.kicker')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-wild-card::sections.cta.heading')) }}
        </h2>
        <p class="exd-lede">
            {{ data_get($section, 'summary', __('capell-theme-wild-card::sections.cta.summary')) }}
        </p>
        <div class="exd-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="exd-button {{ data_get($action, 'style') === 'secondary' ? 'exd-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="exd-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-wild-card::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
