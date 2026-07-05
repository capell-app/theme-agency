@php
    $actions = data_get($section, 'actions', [
        [
            'label' => data_get($section, 'label', __('capell-theme-ink-press::sections.cta.button')),
            'url' => data_get($section, 'url', '/'),
            'style' => 'primary',
        ],
    ]);
@endphp

<section class="dnews-section dnews-section-dark">
    <div class="dnews-section-inner">
        <div class="dnews-section-head dnews-section-head-flush">
            <p class="dnews-kicker">
                {{ __('capell-theme-ink-press::sections.cta.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-ink-press::sections.cta.heading')) }}
            </h2>
            <p class="dnews-lede">
                {{ data_get($section, 'summary', __('capell-theme-ink-press::sections.cta.summary')) }}
            </p>
        </div>
        <div class="dnews-actions">
            @foreach ($actions as $action)
                <a
                    class="dnews-button {{ data_get($action, 'style') === 'secondary' ? 'dnews-button-secondary' : '' }}"
                    href="{{ data_get($action, 'url', '/') }}"
                >
                    {{ data_get($action, 'label', '') }}
                </a>
            @endforeach
        </div>
    </div>
</section>
