@php
    $heading = data_get($section, 'heading', __('capell-theme-outdoor-mission::sections.campaign.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-outdoor-mission::sections.campaign.summary'));
    $actions = data_get($section, 'items', [
        ['title' => __('capell-theme-outdoor-mission::sections.campaign.petition_title'), 'summary' => __('capell-theme-outdoor-mission::sections.campaign.petition_summary')],
        ['title' => __('capell-theme-outdoor-mission::sections.campaign.map_title'), 'summary' => __('capell-theme-outdoor-mission::sections.campaign.map_summary')],
    ]);
@endphp

<section class="outdoor-section outdoor-section-dark">
    <div class="outdoor-section-inner outdoor-split">
        <div>
            <p class="outdoor-kicker">
                {{ __('capell-theme-outdoor-mission::sections.campaign.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="outdoor-lede">{{ $summary }}</p>
            <a
                class="outdoor-button"
                href="{{ data_get($section, 'url', '/') }}"
            >
                {{ data_get($section, 'label', __('capell-theme-outdoor-mission::sections.campaign.button')) }}
            </a>
        </div>
        <div class="outdoor-grid">
            @foreach ($actions as $action)
                <article class="outdoor-card">
                    <h3>
                        {{ data_get($action, 'title', data_get($action, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($action, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
