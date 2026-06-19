@php
    $heading = data_get($section, 'heading', __('capell-theme-resource-hub::sections.updates.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-resource-hub::sections.updates.summary'));
    $actions = data_get($section, 'items', [
        ['title' => __('capell-theme-resource-hub::sections.updates.release_title'), 'summary' => __('capell-theme-resource-hub::sections.updates.release_summary')],
        ['title' => __('capell-theme-resource-hub::sections.updates.beta_title'), 'summary' => __('capell-theme-resource-hub::sections.updates.beta_summary')],
    ]);
@endphp

<section class="editorial-section editorial-section-dark">
    <div class="editorial-section-inner editorial-split">
        <div>
            <p class="editorial-kicker">
                {{ __('capell-theme-resource-hub::sections.updates.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="editorial-lede">{{ $summary }}</p>
            <a
                class="editorial-button"
                href="{{ data_get($section, 'url', '/') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-resource-hub::sections.updates.button')) }}
            </a>
        </div>
        <div class="editorial-grid">
            @foreach ($actions as $action)
                <article class="editorial-card">
                    <h3>
                        {{ data_get($action, 'title', data_get($action, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($action, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
