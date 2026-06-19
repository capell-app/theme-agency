@php
    $heading = data_get($section, 'heading', __('capell-theme-minimal-fashion::sections.lookbook.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-minimal-fashion::sections.lookbook.summary'));
    $actions = data_get($section, 'items', [
        ['title' => __('capell-theme-minimal-fashion::sections.lookbook.silhouette_title'), 'summary' => __('capell-theme-minimal-fashion::sections.lookbook.silhouette_summary')],
        ['title' => __('capell-theme-minimal-fashion::sections.lookbook.detail_title'), 'summary' => __('capell-theme-minimal-fashion::sections.lookbook.detail_summary')],
    ]);
@endphp

<section class="fashion-section fashion-section-dark">
    <div class="fashion-section-inner fashion-split">
        <div>
            <p class="fashion-kicker">
                {{ __('capell-theme-minimal-fashion::sections.lookbook.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="fashion-lede">{{ $summary }}</p>
            <a
                class="fashion-button"
                href="{{ data_get($section, 'url', '/') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-minimal-fashion::sections.lookbook.button')) }}
            </a>
        </div>
        <div class="fashion-grid">
            @foreach ($actions as $action)
                <article class="fashion-card">
                    <h3>
                        {{ data_get($action, 'title', data_get($action, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($action, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
