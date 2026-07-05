@php
    $actions = collect(data_get($section, 'actions', []))
        ->filter(fn (mixed $action): bool => filled(data_get($action, 'label')))
        ->values();
@endphp

<section
    id="cta"
    class="pfd-section pfd-section-night"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-deep-bench::sections.cta.eyebrow')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-deep-bench::sections.cta.heading')) }}
        </h2>
        <p class="pfd-lede">
            {{ data_get($section, 'summary', __('capell-theme-deep-bench::sections.cta.summary')) }}
        </p>
        <div class="pfd-actions">
            @if ($actions->isNotEmpty())
                @foreach ($actions as $action)
                    <a
                        class="pfd-button {{ data_get($action, 'style') === 'secondary' ? 'pfd-button-secondary' : '' }}"
                        href="{{ data_get($action, 'url', '/') }}"
                    >
                        {{ data_get($action, 'label') }}
                    </a>
                @endforeach
            @else
                <a
                    class="pfd-button"
                    href="{{ data_get($section, 'url', '/') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-deep-bench::sections.cta.button')) }}
                </a>
            @endif
        </div>
    </div>
</section>
